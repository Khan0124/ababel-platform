<?php
/**
 * Security Functions Library
 * Provides essential security utilities for the application
 */

declare(strict_types=1);

class SecurityManager 
{
    private static $instance = null;
    private $conn;
    
    private function __construct($database_connection) 
    {
        $this->conn = $database_connection;
    }
    
    public static function getInstance($database_connection = null) 
    {
        if (self::$instance === null) {
            self::$instance = new self($database_connection);
        }
        return self::$instance;
    }
    
    /**
     * Generate a secure CSRF token
     */
    public static function generateCSRFToken(): string 
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken(string $token): bool 
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateSecureToken(int $length = 32): string 
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($input): string 
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return trim(htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail(string $email): bool 
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength(string $password): array 
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على حرف كبير واحد على الأقل';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على حرف صغير واحد على الأقل';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على رمز خاص واحد على الأقل';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password)
        ];
    }
    
    /**
     * Calculate password strength score
     */
    private function calculatePasswordStrength(string $password): int 
    {
        $score = 0;
        
        // Length bonus
        $score += min(strlen($password) * 2, 20);
        
        // Character variety bonus
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 15;
        
        // Pattern penalties
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 10; // Repeated characters
        if (preg_match('/123|abc|qwe/i', $password)) $score -= 10; // Common patterns
        
        return max(0, min(100, $score));
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string 
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword(string $password, string $hash): bool 
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP(): string 
    {
        // Check for IP from shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) ?: 'unknown';
        }
        
        // Check for IP passed from proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            return filter_var($ip, FILTER_VALIDATE_IP) ?: 'unknown';
        }
        
        // Check for IP from remote address
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: 'unknown';
        }
        
        return 'unknown';
    }
    
    /**
     * Get user agent string
     */
    public static function getUserAgent(): string 
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * Check if request is from secure connection
     */
    public static function isSecureConnection(): bool 
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }
    
    /**
     * Rate limiting check
     */
    public function checkRateLimit(string $identifier, int $maxAttempts = 5, int $timeWindow = 300): bool 
    {
        $key = "rate_limit_" . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        $now = time();
        
        // Reset if time window has passed
        if ($now - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
            return true;
        }
        
        // Check if limit exceeded
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Increment attempts
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = $now;
        
        return true;
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent(string $event_type, string $description, array $metadata = []): void 
    {
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            $ip_address = self::getClientIP();
            $user_agent = self::getUserAgent();
            $metadata_json = json_encode($metadata);
            
            $stmt = $this->conn->prepare("
                INSERT INTO security_logs (
                    user_id, event_type, description, ip_address, 
                    user_agent, metadata, timestamp
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("isssss", 
                $user_id, 
                $event_type, 
                $description, 
                $ip_address, 
                $user_agent, 
                $metadata_json
            );
            
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Security logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(string $username): bool 
    {
        try {
            $ip_address = self::getClientIP();
            $timeFrame = date('Y-m-d H:i:s', time() - 3600); // Last hour
            
            // Check for multiple failed attempts from same IP
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as attempts 
                FROM security_logs 
                WHERE ip_address = ? 
                AND event_type = 'failed_login' 
                AND timestamp > ?
            ");
            
            $stmt->bind_param("ss", $ip_address, $timeFrame);
            $stmt->execute();
            $result = $stmt->get_result();
            $ipAttempts = $result->fetch_assoc()['attempts'];
            
            // Check for attempts on multiple accounts from same IP
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT JSON_EXTRACT(metadata, '$.username')) as accounts 
                FROM security_logs 
                WHERE ip_address = ? 
                AND event_type = 'failed_login' 
                AND timestamp > ?
            ");
            
            $stmt->bind_param("ss", $ip_address, $timeFrame);
            $stmt->execute();
            $result = $stmt->get_result();
            $accountAttempts = $result->fetch_assoc()['accounts'];
            
            // Thresholds for suspicious activity
            if ($ipAttempts > 20 || $accountAttempts > 5) {
                $this->logSecurityEvent('suspicious_activity', 'High number of failed login attempts', [
                    'ip_attempts' => $ipAttempts,
                    'account_attempts' => $accountAttempts,
                    'username' => $username
                ]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Suspicious activity check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired sessions and tokens
     */
    public function cleanupExpiredData(): void 
    {
        try {
            // Clean expired remember tokens
            $stmt = $this->conn->prepare("DELETE FROM remember_tokens WHERE expires < NOW()");
            $stmt->execute();
            
            // Clean old security logs (keep for 90 days)
            $stmt = $this->conn->prepare("
                DELETE FROM security_logs 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)
            ");
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Cleanup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Generate secure session ID
     */
    public static function generateSessionId(): string 
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate session security
     */
    public static function validateSession(): bool 
    {
        // Check if session exists
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout (30 minutes of inactivity)
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > 1800) {
            session_destroy();
            return false;
        }
        
        // Check session IP consistency (optional - may cause issues with mobile users)
        if (isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== self::getClientIP()) {
            // Log potential session hijacking
            error_log("Session IP mismatch: " . $_SESSION['ip_address'] . " vs " . self::getClientIP());
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Secure session start
     */
    public static function startSecureSession(): void 
    {
        // Configure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', self::isSecureConnection() ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize session security data
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateCSRFToken();
        }
        
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = self::getClientIP();
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Destroy session securely
     */
    public static function destroySession(): void 
    {
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission): bool 
    {
        if (!isset($_SESSION['role'])) {
            return false;
        }
        
        $role = $_SESSION['role'];
        
        // Define role permissions
        $permissions = [
            'admin' => ['*'], // Admin has all permissions
            'manager' => ['view_reports', 'manage_users', 'edit_data'],
            'user' => ['view_data', 'edit_own_data'],
            'guest' => ['view_public_data']
        ];
        
        if (!isset($permissions[$role])) {
            return false;
        }
        
        return in_array('*', $permissions[$role]) || 
               in_array($permission, $permissions[$role]);
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth(): void 
    {
        if (!self::validateSession()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Require specific permission
     */
    public static function requirePermission(string $permission): void 
    {
        self::requireAuth();
        
        if (!self::hasPermission($permission)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: access_denied.php');
            exit;
        }
    }
}

// Utility functions for backwards compatibility
function sanitize_input($input) {
    return SecurityManager::sanitizeInput($input);
}

function generate_csrf_token() {
    return SecurityManager::generateCSRFToken();
}

function validate_csrf_token($token) {
    return SecurityManager::validateCSRFToken($token);
}

function get_client_ip() {
    return SecurityManager::getClientIP();
}

function hash_password($password) {
    return SecurityManager::hashPassword($password);
}

function verify_password($password, $hash) {
    return SecurityManager::verifyPassword($password, $hash);
}

function require_auth() {
    SecurityManager::requireAuth();
}

function require_permission($permission) {
    SecurityManager::requirePermission($permission);
}

function has_permission($permission) {
    return SecurityManager::hasPermission($permission);
}

// Auto-start secure session
SecurityManager::startSecureSession();

// Initialize security manager instance with database connection
if (isset($conn)) {
    SecurityManager::getInstance($conn);
}