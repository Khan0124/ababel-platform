<?php
namespace App\Core;

/**
 * Security Manager
 * Handles all security-related functionality including CSRF, input validation, and authentication
 */
class SecurityManager
{
    private static $instance = null;
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate a secure CSRF token
     */
    public function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRFToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Sanitize input data
     */
    public function sanitizeInput($input): string
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return trim(htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Validate email format
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
    
    /**
     * Hash password using Argon2id (preferred) or bcrypt
     */
    public function hashPassword(string $password): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        }
        
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Get client IP address
     */
    public function getClientIP(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    /**
     * Check if connection is secure (HTTPS)
     */
    public function isSecureConnection(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }
    
    /**
     * Rate limiting check
     */
    public function checkRateLimit(string $identifier, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $db = Database::getInstance();
        
        try {
            // Clean old entries
            $db->query("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)", [$timeWindow]);
            
            // Check current attempts
            $stmt = $db->query("SELECT COUNT(*) as count FROM rate_limits WHERE identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)", 
                              [$identifier, $timeWindow]);
            $result = $stmt->fetch();
            
            if ($result['count'] >= $maxAttempts) {
                return false;
            }
            
            // Log this attempt
            $db->query("INSERT INTO rate_limits (identifier, ip_address, user_agent, created_at) VALUES (?, ?, ?, NOW())",
                      [$identifier, $this->getClientIP(), $this->getUserAgent()]);
            
            return true;
            
        } catch (Exception $e) {
            // If rate limiting fails, allow the request but log the error
            error_log("Rate limiting error: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent(string $eventType, string $description, array $metadata = []): void
    {
        $db = Database::getInstance();
        
        try {
            $sql = "INSERT INTO security_log (event_type, description, ip_address, user_agent, user_id, metadata, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $db->query($sql, [
                $eventType,
                $description,
                $this->getClientIP(),
                $this->getUserAgent(),
                $_SESSION['user_id'] ?? null,
                json_encode($metadata)
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(string $username): bool
    {
        $db = Database::getInstance();
        
        try {
            // Check for multiple failed login attempts
            $stmt = $db->query("SELECT COUNT(*) as count FROM security_log 
                               WHERE event_type = 'failed_login' 
                               AND description LIKE ? 
                               AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                              ["%$username%"]);
            
            $result = $stmt->fetch();
            
            if ($result['count'] > 10) {
                return true;
            }
            
            // Check for unusual IP patterns
            $stmt = $db->query("SELECT COUNT(DISTINCT ip_address) as count FROM security_log 
                               WHERE event_type = 'failed_login' 
                               AND description LIKE ? 
                               AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                              ["%$username%"]);
            
            $result = $stmt->fetch();
            
            if ($result['count'] > 5) {
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Suspicious activity check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired security data
     */
    public function cleanupExpiredData(): void
    {
        $db = Database::getInstance();
        
        try {
            // Clean up old rate limit entries (older than 1 hour)
            $db->query("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            
            // Clean up old security logs (older than 30 days)
            $db->query("DELETE FROM security_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            
            // Clean up old activity logs (older than 90 days)
            $db->query("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
            
        } catch (Exception $e) {
            error_log("Cleanup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Generate secure session ID
     */
    public function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate and regenerate session if needed
     */
    public function validateSession(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['last_regeneration']) || 
            time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        return true;
    }
    
    /**
     * Start secure session
     */
    public function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $this->isSecureConnection() ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_lifetime', 0); // Session cookie
            
            session_start();
        }
    }
    
    /**
     * Destroy session securely
     */
    public function destroySession(): void
    {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $roleHierarchy = [
            'viewer' => 1,
            'accountant' => 2,
            'admin' => 3
        ];
        
        $userRole = $_SESSION['user_role'] ?? 'viewer';
        $requiredLevel = $roleHierarchy[$permission] ?? 0;
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Require authentication
     */
    public function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                header('Location: /login');
                exit;
            }
        }
    }
    
    /**
     * Require specific permission
     */
    public function requirePermission(string $permission): void
    {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Insufficient permissions']);
                exit;
            } else {
                header('Location: /unauthorized');
                exit;
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
} 