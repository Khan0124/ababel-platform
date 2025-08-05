<?php
// نظام إدارة الجلسات المتقدم
require_once 'security.php';

class SessionManager {
    
    private $conn;
    private $security;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
        $this->security = new SecurityManager($database_connection);
        
        // إعدادات الجلسة الآمنة (تطبق فقط إذا الجلسة غير نشطة)
        if (session_status() == PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            session_start();
        }
    }
    
    // تسجيل دخول آمن للإدارة
    public function adminLogin($email, $password) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // فحص محاولات تسجيل الدخول المتكررة
        if ($this->security->checkBruteForce($ip_address)) {
            $this->security->logSecurityEvent('brute_force_attempt', 'Too many login attempts', $ip_address, $user_agent);
            throw new Exception('تم حظر IP الخاص بك مؤقتاً بسبب محاولات تسجيل دخول متكررة');
        }
        
        // البحث عن المشرف (case-insensitive)
        $stmt = $this->conn->prepare("SELECT id, name, email, password FROM admins WHERE LOWER(email) = LOWER(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($admin = $result->fetch_assoc()) {
            if ($this->security->verifyPassword($password, $admin['password'])) {
                // تسجيل دخول ناجح
                $session_token = $this->createSession($admin['id'], 'admin', $ip_address, $user_agent);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['session_token'] = $session_token;
                $_SESSION['user_type'] = 'admin';
                $_SESSION['login_time'] = time();
                
                // تحديث آخر تسجيل دخول
                $this->updateLastLogin($admin['id'], 'admin');
                
                $this->security->logSecurityEvent('successful_login', 'Admin login successful', $ip_address, $user_agent);
                
                return [
                    'success' => true,
                    'admin_id' => $admin['id'],
                    'name' => $admin['name']
                ];
            }
        }
        
        // تسجيل محاولة دخول فاشلة
        $this->security->logSecurityEvent('failed_login', 'Invalid credentials for: ' . $email, $ip_address, $user_agent);
        
        throw new Exception('البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }
    
    // تسجيل دخول آمن للموظفين
    public function employeeLogin($username, $password) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // فحص محاولات تسجيل الدخول المتكررة
        if ($this->security->checkBruteForce($ip_address)) {
            $this->security->logSecurityEvent('brute_force_attempt', 'Too many login attempts', $ip_address, $user_agent);
            throw new Exception('تم حظر IP الخاص بك مؤقتاً بسبب محاولات تسجيل دخول متكررة');
        }
        
        // البحث عن الموظف (case-insensitive)
        $stmt = $this->conn->prepare("SELECT le.id, le.name, le.username, le.password, le.role, le.lab_id, l.name as lab_name, l.status as lab_status 
                                     FROM lab_employees le 
                                     JOIN labs l ON le.lab_id = l.id 
                                     WHERE LOWER(le.username) = LOWER(?) AND (le.status = 'active' OR le.status = 'نشط')");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($employee = $result->fetch_assoc()) {
            // التحقق من حالة المختبر
            if ($employee['lab_status'] !== 'active' && $employee['lab_status'] !== 'نشط') {
                throw new Exception('المختبر غير مفعل حالياً');
            }
            
            if ($this->security->verifyPassword($password, $employee['password'])) {
                // تسجيل دخول ناجح
                $session_token = $this->createSession($employee['id'], 'lab_employee', $ip_address, $user_agent);
                
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_username'] = $employee['username'];
                $_SESSION['lab_id'] = $employee['lab_id'];
                $_SESSION['lab_name'] = $employee['lab_name'];
                $_SESSION['employee_role'] = $employee['role'];
                $_SESSION['session_token'] = $session_token;
                $_SESSION['user_type'] = 'lab_employee';
                $_SESSION['login_time'] = time();
                
                // تحديث آخر تسجيل دخول
                $this->updateLastLogin($employee['id'], 'lab_employee');
                
                $this->security->logSecurityEvent('successful_login', 'Employee login successful', $ip_address, $user_agent);
                
                return [
                    'success' => true,
                    'employee_id' => $employee['id'], 
                    'name' => $employee['name'],
                    'lab_id' => $employee['lab_id'],
                    'lab_name' => $employee['lab_name']
                ];
            }
        }
        
        // تسجيل محاولة دخول فاشلة
        $this->security->logSecurityEvent('failed_login', 'Invalid credentials for: ' . $username, $ip_address, $user_agent);
        
        throw new Exception('اسم المستخدم أو كلمة المرور غير صحيحة');
    }
    
    // إنشاء جلسة جديدة
    private function createSession($user_id, $user_type, $ip_address, $user_agent) {
        // إنهاء الجلسات القديمة للمستخدم
        $this->terminateOldSessions($user_id, $user_type);
        
        // إنشاء رمز جلسة قوي
        $session_token = bin2hex(random_bytes(64));
        
        // حفظ الجلسة في قاعدة البيانات
        $stmt = $this->conn->prepare("INSERT INTO user_sessions (user_id, user_type, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $user_type, $session_token, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return $session_token;
    }
    
    // إنهاء الجلسات القديمة
    private function terminateOldSessions($user_id, $user_type) {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET is_active = 0, ended_at = NOW() WHERE user_id = ? AND user_type = ? AND is_active = 1");
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $stmt->close();
    }
    
    // التحقق من الجلسة
    public function validateSession() {
        if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_type'])) {
            return false;
        }
        
        $user_id = $_SESSION['user_type'] === 'admin' ? ($_SESSION['admin_id'] ?? null) : ($_SESSION['employee_id'] ?? null);
        
        if (!$user_id) {
            return false;
        }
        
        return $this->security->validateSession($user_id, $_SESSION['session_token']);
    }
    
    // تسجيل خروج آمن
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            $user_id = $_SESSION['user_type'] === 'admin' ? $_SESSION['admin_id'] : $_SESSION['employee_id'];
            $this->security->terminateSession($user_id);
            
            $this->security->logSecurityEvent('logout', 'User logged out', $_SERVER['REMOTE_ADDR']);
        }
        
        // تنظيف الجلسة
        session_unset();
        session_destroy();
        
        // حذف كوكيز الجلسة
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    // تحديث آخر تسجيل دخول
    private function updateLastLogin($user_id, $user_type) {
        if ($user_type === 'admin') {
            $stmt = $this->conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        } else {
            $stmt = $this->conn->prepare("UPDATE lab_employees SET last_login = NOW() WHERE id = ?");
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // تغيير كلمة المرور
    public function changePassword($user_id, $user_type, $old_password, $new_password) {
        // التحقق من قوة كلمة المرور الجديدة
        $validation = $this->security->validatePasswordStrength($new_password);
        if ($validation !== true) {
            throw new Exception(implode("\n", $validation));
        }
        
        // التحقق من كلمة المرور القديمة
        if ($user_type === 'admin') {
            $stmt = $this->conn->prepare("SELECT password FROM admins WHERE id = ?");
        } else {
            $stmt = $this->conn->prepare("SELECT password FROM lab_employees WHERE id = ?");
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!$this->security->verifyPassword($old_password, $user['password'])) {
            throw new Exception('كلمة المرور القديمة غير صحيحة');
        }
        
        // تحديث كلمة المرور
        $new_hash = $this->security->hashPassword($new_password);
        
        if ($user_type === 'admin') {
            $stmt = $this->conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
        } else {
            $stmt = $this->conn->prepare("UPDATE lab_employees SET password = ? WHERE id = ?");
        }
        
        $stmt->bind_param("si", $new_hash, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // تسجيل تغيير كلمة المرور
        $this->security->logSecurityEvent('password_change', 'Password changed successfully', $_SERVER['REMOTE_ADDR']);
        
        return true;
    }
    
    // تنظيف الجلسات المنتهية الصلاحية
    public function cleanupExpiredSessions() {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET is_active = 0, ended_at = NOW() WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND is_active = 1");
        $stmt->execute();
        $stmt->close();
    }
}
?>