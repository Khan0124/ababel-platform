<?php
// نظام الأمان المحسن لنظام إدارة المختبرات
class SecurityManager {
    
    private $conn;
    private $encryption_key;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
        $this->encryption_key = $this->getEncryptionKey();
    }
    
    // تشفير كلمات المرور بـ Argon2ID (أقوى من bcrypt)
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }
    
    // التحقق من كلمة المرور
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // تشفير البيانات الحساسة
    public function encryptData($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-GCM', $this->encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $encrypted);
    }
    
    // فك تشفير البيانات
    public function decryptData($encryptedData) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-GCM', $this->encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
        return $decrypted !== false ? $decrypted : null;
    }
    
    // تنظيف المدخلات من XSS
    public function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    // التحقق من CSRF Token
    public function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public function verifyCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // تسجيل محاولات تسجيل الدخول المشبوهة
    public function logSecurityEvent($event_type, $description, $ip_address, $user_agent = null) {
        $stmt = $this->conn->prepare("INSERT INTO security_logs (event_type, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $event_type, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
    
    // فحص محاولات تسجيل الدخول المتكررة (Brute Force Protection)
    public function checkBruteForce($ip_address, $max_attempts = 5, $time_window = 900) { // 15 دقيقة
        $stmt = $this->conn->prepare("SELECT COUNT(*) as attempts FROM security_logs WHERE ip_address = ? AND event_type = 'failed_login' AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("si", $ip_address, $time_window);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['attempts'] >= $max_attempts;
    }
    
    // حماية من حقن SQL المتقدمة
    public function sanitizeForSQL($input) {
        return $this->conn->real_escape_string($this->sanitizeInput($input));
    }
    
    // التحقق من قوة كلمة المرور
    public function validatePasswordStrength($password) {
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
        
        return empty($errors) ? true : $errors;
    }
    
    // جلب مفتاح التشفير (يتم إنشاؤه تلقائياً)
    private function getEncryptionKey() {
        $key_file = __DIR__ . '/encryption.key';
        
        if (!file_exists($key_file)) {
            $key = random_bytes(32);
            file_put_contents($key_file, base64_encode($key));
            chmod($key_file, 0600); // قراءة فقط للمالك
        } else {
            $key = base64_decode(file_get_contents($key_file));
        }
        
        return $key;
    }
    
    // تنظيف محفوظات الأمان القديمة (تشغل يومياً)
    public function cleanupSecurityLogs($days = 30) {
        $stmt = $this->conn->prepare("DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $stmt->close();
    }
    
    // التحقق من صحة الجلسة
    public function validateSession($user_id, $session_token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $stmt = $this->conn->prepare("SELECT session_token, last_activity, ip_address FROM user_sessions WHERE user_id = ? AND is_active = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // التحقق من انتهاء الجلسة (30 دقيقة عدم نشاط)
            $last_activity = strtotime($row['last_activity']);
            $current_time = time();
            
            if (($current_time - $last_activity) > 1800) { // 30 دقيقة
                $this->terminateSession($user_id);
                return false;
            }
            
            // التحقق من IP Address
            if ($row['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                $this->logSecurityEvent('session_hijack', 'IP address mismatch', $_SERVER['REMOTE_ADDR']);
                $this->terminateSession($user_id);
                return false;
            }
            
            // تحديث وقت آخر نشاط
            $this->updateSessionActivity($user_id);
            return hash_equals($row['session_token'], $session_token);
        }
        
        $stmt->close();
        return false;
    }
    
    // إنهاء الجلسة
    public function terminateSession($user_id) {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET is_active = 0, ended_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // تحديث نشاط الجلسة
    private function updateSessionActivity($user_id) {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE user_id = ? AND is_active = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// إنشاء جداول الأمان الجديدة
function createSecurityTables($conn) {
    
    // جدول سجلات الأمان
    $security_logs_sql = "CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        user_id INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_event_type (event_type),
        INDEX idx_ip_address (ip_address),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // جدول الجلسات المحسن
    $user_sessions_sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('admin', 'lab_employee') NOT NULL,
        session_token VARCHAR(128) NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
        ended_at DATETIME NULL,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX idx_user_id (user_id),
        INDEX idx_session_token (session_token),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($security_logs_sql);
    $conn->query($user_sessions_sql);
}

// تشغيل إنشاء الجداول
if (isset($conn)) {
    createSecurityTables($conn);
}
?>