<?php
namespace App\Services;

use App\Core\Security;
use App\Models\Lab;
use App\Models\LabEmployee;
use PDO;
use Exception;

class AuthService {
    private $db;
    private $security;
    private $labModel;
    private $employeeModel;
    
    public function __construct(PDO $database) {
        $this->db = $database;
        $this->security = new Security($database);
        $this->labModel = new Lab($database);
        $this->employeeModel = new LabEmployee($database);
    }
    
    /**
     * Authenticate lab login
     */
    public function authenticateLab(string $email, string $password): array {
        $ip = $this->security->getClientIP();
        
        // Check for brute force attacks
        if ($this->security->checkBruteForce($ip)) {
            $this->security->logSecurityEvent('brute_force_blocked', 'Too many failed login attempts', $ip);
            throw new Exception('تم حظر هذا العنوان IP مؤقتاً بسبب محاولات تسجيل دخول متكررة. يرجى المحاولة لاحقاً.');
        }
        
        // Find lab by email
        $lab = $this->labModel->findBy('email', $email);
        if (!$lab) {
            $this->security->logSecurityEvent('failed_login', "Failed login attempt for email: {$email}", $ip);
            throw new Exception('بيانات تسجيل الدخول غير صحيحة');
        }
        
        // Check if lab is active
        if (!$lab['status']) {
            $this->security->logSecurityEvent('inactive_lab_login', "Login attempt for inactive lab: {$email}", $ip);
            throw new Exception('هذا المعمل غير مفعل حالياً');
        }
        
        // Verify password
        if (!$this->security->verifyPassword($password, $lab['password'])) {
            $this->security->logSecurityEvent('failed_login', "Failed login attempt for lab: {$email}", $ip);
            throw new Exception('بيانات تسجيل الدخول غير صحيحة');
        }
        
        // Create session
        $sessionToken = $this->security->generateSecureToken();
        $this->createLabSession($lab['id'], $sessionToken);
        
        // Log successful login
        $this->security->logSecurityEvent('successful_login', "Successful lab login: {$email}", $ip);
        
        return [
            'lab_id' => $lab['id'],
            'lab_name' => $lab['name'],
            'session_token' => $sessionToken,
            'subscription_type' => $lab['subscription_type'],
            'subscription_end_date' => $lab['subscription_end_date']
        ];
    }
    
    /**
     * Authenticate employee login
     */
    public function authenticateEmployee(int $labId, string $username, string $password): array {
        $ip = $this->security->getClientIP();
        
        // Check for brute force attacks
        if ($this->security->checkBruteForce($ip)) {
            $this->security->logSecurityEvent('brute_force_blocked', 'Too many failed login attempts', $ip);
            throw new Exception('تم حظر هذا العنوان IP مؤقتاً بسبب محاولات تسجيل دخول متكررة. يرجى المحاولة لاحقاً.');
        }
        
        // Find employee by username and lab_id
        $employee = $this->employeeModel->findByUsernameAndLab($username, $labId);
        if (!$employee) {
            $this->security->logSecurityEvent('failed_login', "Failed employee login attempt: {$username}", $ip);
            throw new Exception('بيانات تسجيل الدخول غير صحيحة');
        }
        
        // Check if employee is active
        if (!$employee['status']) {
            $this->security->logSecurityEvent('inactive_employee_login', "Login attempt for inactive employee: {$username}", $ip);
            throw new Exception('هذا الموظف غير مفعل حالياً');
        }
        
        // Verify password
        if (!$this->security->verifyPassword($password, $employee['password'])) {
            $this->security->logSecurityEvent('failed_login', "Failed employee login attempt: {$username}", $ip);
            throw new Exception('بيانات تسجيل الدخول غير صحيحة');
        }
        
        // Create session
        $sessionToken = $this->security->generateSecureToken();
        $this->createEmployeeSession($employee['id'], $sessionToken);
        
        // Log successful login
        $this->security->logSecurityEvent('successful_login', "Successful employee login: {$username}", $ip);
        
        return [
            'employee_id' => $employee['id'],
            'employee_name' => $employee['name'],
            'lab_id' => $employee['lab_id'],
            'role' => $employee['role'],
            'session_token' => $sessionToken
        ];
    }
    
    /**
     * Validate session
     */
    public function validateSession(int $userId, string $sessionToken, string $userType = 'lab'): bool {
        $table = $userType === 'lab' ? 'lab_sessions' : 'employee_sessions';
        
        $sql = "
            SELECT id, last_activity 
            FROM {$table} 
            WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $sessionToken]);
        $session = $stmt->fetch();
        
        if (!$session) {
            return false;
        }
        
        // Update last activity
        $stmt = $this->db->prepare("
            UPDATE {$table} 
            SET last_activity = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$session['id']]);
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout(int $userId, string $sessionToken, string $userType = 'lab'): void {
        $table = $userType === 'lab' ? 'lab_sessions' : 'employee_sessions';
        
        $stmt = $this->db->prepare("
            DELETE FROM {$table} 
            WHERE user_id = ? AND session_token = ?
        ");
        $stmt->execute([$userId, $sessionToken]);
        
        $this->security->logSecurityEvent('logout', "User logged out: {$userId}", $this->security->getClientIP());
    }
    
    /**
     * Create lab session
     */
    private function createLabSession(int $labId, string $sessionToken): void {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        $stmt = $this->db->prepare("
            INSERT INTO lab_sessions (lab_id, session_token, expires_at, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$labId, $sessionToken, $expiresAt]);
    }
    
    /**
     * Create employee session
     */
    private function createEmployeeSession(int $employeeId, string $sessionToken): void {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+8 hours'));
        
        $stmt = $this->db->prepare("
            INSERT INTO employee_sessions (employee_id, session_token, expires_at, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$employeeId, $sessionToken, $expiresAt]);
    }
    
    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $userType = 'lab'): bool {
        $table = $userType === 'lab' ? 'labs' : 'lab_employees';
        
        // Get current user
        $stmt = $this->db->prepare("SELECT password FROM {$table} WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('المستخدم غير موجود');
        }
        
        // Verify current password
        if (!$this->security->verifyPassword($currentPassword, $user['password'])) {
            throw new Exception('كلمة المرور الحالية غير صحيحة');
        }
        
        // Validate new password strength
        $errors = $this->security->validatePasswordStrength($newPassword);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        // Hash new password
        $hashedPassword = $this->security->hashPassword($newPassword);
        
        // Update password
        $stmt = $this->db->prepare("UPDATE {$table} SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $userId]);
        
        if ($result) {
            $this->security->logSecurityEvent('password_changed', "Password changed for user: {$userId}", $this->security->getClientIP());
        }
        
        return $result;
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): void {
        $this->db->exec("DELETE FROM lab_sessions WHERE expires_at < NOW()");
        $this->db->exec("DELETE FROM employee_sessions WHERE expires_at < NOW()");
    }
} 