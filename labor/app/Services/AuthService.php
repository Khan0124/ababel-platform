<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Employee;
use App\Models\Lab;

class AuthService
{
    private $sessionPrefix = 'auth_';
    private $maxAttempts = 5;
    private $lockoutMinutes = 15;
    
    public function __construct()
    {
        $this->maxAttempts = $_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5;
        $this->lockoutMinutes = $_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15;
    }
    
    public function attemptAdminLogin($email, $password, $remember = false)
    {
        // Check rate limiting
        if ($this->isLockedOut('admin', $email)) {
            return [
                'success' => false,
                'message' => $this->getRateLimitMessage('admin', $email)
            ];
        }
        
        $result = Admin::authenticate($email, $password);
        
        if ($result['success']) {
            $this->clearLoginAttempts('admin', $email);
            $this->createAdminSession($result['admin'], $remember);
            $result['admin']->logActivity('login', 'تسجيل دخول ناجح');
        } else {
            $this->incrementLoginAttempts('admin', $email);
        }
        
        return $result;
    }
    
    public function attemptEmployeeLogin($email, $password, $remember = false)
    {
        // Check rate limiting
        if ($this->isLockedOut('employee', $email)) {
            return [
                'success' => false,
                'message' => $this->getRateLimitMessage('employee', $email)
            ];
        }
        
        $result = Employee::authenticate($email, $password);
        
        if ($result['success']) {
            $this->clearLoginAttempts('employee', $email);
            $this->createEmployeeSession($result['employee'], $remember);
            $result['employee']->logActivity('login', 'تسجيل دخول ناجح');
        } else {
            $this->incrementLoginAttempts('employee', $email);
        }
        
        return $result;
    }
    
    private function createAdminSession($admin, $remember = false)
    {
        $_SESSION['admin_id'] = $admin->id;
        $_SESSION['admin_name'] = $admin->name;
        $_SESSION['admin_email'] = $admin->email;
        $_SESSION['admin_role'] = $admin->role;
        $_SESSION['auth_type'] = 'admin';
        $_SESSION['auth_time'] = time();
        
        if ($remember) {
            $this->createRememberToken('admin', $admin->id);
        }
        
        $this->regenerateSession();
    }
    
    private function createEmployeeSession($employee, $remember = false)
    {
        $_SESSION['employee_id'] = $employee->id;
        $_SESSION['employee_name'] = $employee->name;
        $_SESSION['employee_email'] = $employee->email;
        $_SESSION['employee_role'] = $employee->role;
        $_SESSION['lab_id'] = $employee->lab_id;
        $_SESSION['auth_type'] = 'employee';
        $_SESSION['auth_time'] = time();
        
        if ($remember) {
            $this->createRememberToken('employee', $employee->id);
        }
        
        $this->regenerateSession();
    }
    
    private function regenerateSession()
    {
        session_regenerate_id(true);
        $_SESSION['fingerprint'] = $this->generateFingerprint();
    }
    
    private function generateFingerprint()
    {
        return hash('sha256', 
            $_SERVER['HTTP_USER_AGENT'] . 
            $_SERVER['REMOTE_ADDR'] . 
            $_ENV['APP_KEY']
        );
    }
    
    public function validateSession()
    {
        // Check session fingerprint
        if (!isset($_SESSION['fingerprint']) || 
            $_SESSION['fingerprint'] !== $this->generateFingerprint()) {
            return false;
        }
        
        // Check session timeout
        $lifetime = ($_ENV['SESSION_LIFETIME'] ?? 120) * 60;
        if (isset($_SESSION['auth_time']) && 
            (time() - $_SESSION['auth_time']) > $lifetime) {
            return false;
        }
        
        return true;
    }
    
    public function checkAdmin()
    {
        return isset($_SESSION['auth_type']) && 
               $_SESSION['auth_type'] === 'admin' &&
               isset($_SESSION['admin_id']) &&
               $this->validateSession();
    }
    
    public function checkEmployee()
    {
        return isset($_SESSION['auth_type']) && 
               $_SESSION['auth_type'] === 'employee' &&
               isset($_SESSION['employee_id']) &&
               $this->validateSession();
    }
    
    public function getCurrentAdmin()
    {
        if ($this->checkAdmin()) {
            return Admin::find($_SESSION['admin_id']);
        }
        return null;
    }
    
    public function getCurrentEmployee()
    {
        if ($this->checkEmployee()) {
            return Employee::find($_SESSION['employee_id']);
        }
        return null;
    }
    
    public function logout()
    {
        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Log the logout activity
        if ($this->checkAdmin()) {
            $admin = $this->getCurrentAdmin();
            $admin->logActivity('logout', 'تسجيل خروج');
        } elseif ($this->checkEmployee()) {
            $employee = $this->getCurrentEmployee();
            $employee->logActivity('logout', 'تسجيل خروج');
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session for flash messages
        session_start();
    }
    
    private function isLockedOut($type, $email)
    {
        $key = $this->getAttemptsKey($type, $email);
        $attempts = $_SESSION[$key]['attempts'] ?? 0;
        $lastAttempt = $_SESSION[$key]['last_attempt'] ?? 0;
        
        if ($attempts >= $this->maxAttempts) {
            $lockoutTime = $this->lockoutMinutes * 60;
            if ((time() - $lastAttempt) < $lockoutTime) {
                return true;
            } else {
                // Reset attempts after lockout period
                unset($_SESSION[$key]);
            }
        }
        
        return false;
    }
    
    private function incrementLoginAttempts($type, $email)
    {
        $key = $this->getAttemptsKey($type, $email);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
        }
        
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = time();
    }
    
    private function clearLoginAttempts($type, $email)
    {
        $key = $this->getAttemptsKey($type, $email);
        unset($_SESSION[$key]);
    }
    
    private function getAttemptsKey($type, $email)
    {
        return 'login_attempts_' . $type . '_' . md5($email);
    }
    
    private function getRateLimitMessage($type, $email)
    {
        $key = $this->getAttemptsKey($type, $email);
        $lastAttempt = $_SESSION[$key]['last_attempt'] ?? 0;
        $remainingTime = ($this->lockoutMinutes * 60) - (time() - $lastAttempt);
        $minutes = ceil($remainingTime / 60);
        
        return "تم تجاوز عدد المحاولات المسموح. يرجى المحاولة بعد {$minutes} دقيقة";
    }
    
    private function createRememberToken($type, $userId)
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (you'll need a remember_tokens table)
        // For now, we'll use a simple cookie
        $value = $type . '|' . $userId . '|' . $token;
        setcookie('remember_token', $value, $expires, '/', '', true, true);
    }
}