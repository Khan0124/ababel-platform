<?php
namespace App\Core;

use PDO;
use Exception;

class Security {
    private $db;
    private $encryption_key;
    
    public function __construct(PDO $database) {
        $this->db = $database;
        $this->encryption_key = \App\Config\App::get('ENCRYPTION_KEY');
    }
    
    /**
     * Hash password using Argon2id (strongest available)
     */
    public function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Encrypt sensitive data using AES-256-GCM
     */
    public function encryptData(string $data): string {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-GCM', $this->encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decryptData(string $encryptedData): ?string {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-GCM', $this->encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
        return $decrypted !== false ? $decrypted : null;
    }
    
    /**
     * Sanitize input to prevent XSS
     */
    public function sanitizeInput($input): string {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken(): string {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken(string $token): bool {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent(string $event_type, string $description, string $ip_address, ?string $user_agent = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO security_logs (event_type, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$event_type, $description, $ip_address, $user_agent]);
    }
    
    /**
     * Check for brute force attacks
     */
    public function checkBruteForce(string $ip_address, int $max_attempts = 5, int $time_window = 900): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts 
            FROM security_logs 
            WHERE ip_address = ? AND event_type = 'failed_login' 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip_address, $time_window]);
        $result = $stmt->fetch();
        
        return $result['attempts'] >= $max_attempts;
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "كلمة المرور يجب أن تكون 8 أحرف على الأقل";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "كلمة المرور يجب أن تحتوي على حرف كبير واحد على الأقل";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "كلمة المرور يجب أن تحتوي على حرف صغير واحد على الأقل";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "كلمة المرور يجب أن تحتوي على رقم واحد على الأقل";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "كلمة المرور يجب أن تحتوي على رمز خاص واحد على الأقل";
        }
        
        return $errors;
    }
    
    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate email format
     */
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Clean up old security logs
     */
    public function cleanupSecurityLogs(int $days = 30): void {
        $stmt = $this->db->prepare("
            DELETE FROM security_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
    }
    
    /**
     * Get client IP address
     */
    public function getClientIP(): string {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
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
     * Validate session
     */
    public function validateSession(int $user_id, string $session_token): bool {
        $stmt = $this->db->prepare("
            SELECT id, last_activity 
            FROM user_sessions 
            WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$user_id, $session_token]);
        $session = $stmt->fetch();
        
        if (!$session) {
            return false;
        }
        
        // Update last activity
        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$session['id']]);
        
        return true;
    }
    
    /**
     * Terminate user session
     */
    public function terminateSession(int $user_id): void {
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
    }
} 