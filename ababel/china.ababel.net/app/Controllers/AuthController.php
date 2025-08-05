<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\SecurityManager;
use App\Models\User;
use Exception;

/**
 * Authentication Controller
 * Handles user authentication, login, logout, and profile management
 */
class AuthController extends Controller
{
    private $security;
    
    public function __construct()
    {
        parent::__construct();
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Display login form or handle login POST request
     */
    public function login()
    {
        if ($this->isPost()) {
            $this->handleLogin();
        } else {
            $this->showLoginForm();
        }
    }
    
    /**
     * Handle login form submission
     */
    private function handleLogin()
    {
        try {
            // Validate CSRF token
            $this->validateCSRF();
            
            // Get and validate input
            $username = $this->getPost('username');
            $password = $this->getPost('password');
            
            if (empty($username) || empty($password)) {
                throw new Exception('Username and password are required');
            }
            
            // Rate limiting for login attempts
            $identifier = "login_attempt_" . $this->security->getClientIP();
            if (!$this->security->checkRateLimit($identifier, 5, 300)) {
                $this->security->logSecurityEvent('rate_limit_exceeded', 'Too many login attempts', [
                    'username' => $username,
                    'ip' => $this->security->getClientIP()
                ]);
                throw new Exception('Too many login attempts. Please try again later.');
            }
            
            // Check for suspicious activity
            if ($this->security->checkSuspiciousActivity($username)) {
                $this->security->logSecurityEvent('suspicious_activity', 'Suspicious login attempt detected', [
                    'username' => $username,
                    'ip' => $this->security->getClientIP()
                ]);
                throw new Exception('Account temporarily locked due to suspicious activity.');
            }
            
            // Authenticate user
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if (!$user || !$this->security->verifyPassword($password, $user['password'])) {
                $this->security->logSecurityEvent('failed_login', "Failed login attempt for username: $username", [
                    'username' => $username,
                    'ip' => $this->security->getClientIP()
                ]);
                throw new Exception('Invalid username or password');
            }
            
            // Check if user is active
            if (!$user['is_active']) {
                throw new Exception('Account is deactivated. Please contact administrator.');
            }
            
            // Successful login
            $this->createUserSession($user);
            
            // Update last login
            $userModel->updateLastLogin($user['id']);
            
            // Log successful login
            $this->logActivity('login', "User logged in successfully", [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
            
            // Redirect to dashboard
            $this->redirect('/dashboard');
            
        } catch (Exception $e) {
            $this->view('auth/login', [
                'title' => 'تسجيل الدخول',
                'error' => $e->getMessage(),
                'csrf_token' => $this->generateCSRFToken()
            ]);
        }
    }
    
    /**
     * Display login form
     */
    private function showLoginForm()
    {
        $this->view('auth/login', [
            'title' => 'تسجيل الدخول',
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    /**
     * Create user session after successful login
     */
    private function createUserSession($user)
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regeneration'] = time();
        
        // Log successful login
        $this->security->logSecurityEvent('successful_login', "User logged in successfully", [
            'user_id' => $user['id'],
            'username' => $user['username']
        ]);
    }
    
    /**
     * Handle user logout
     */
    public function logout()
    {
        try {
            // Log logout activity
            if (isset($_SESSION['user_id'])) {
                $this->logActivity('logout', "User logged out", [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'] ?? 'unknown'
                ]);
            }
            
            // Destroy session securely
            $this->security->destroySession();
            
            // Redirect to login page
            $this->redirect('/login');
            
        } catch (Exception $e) {
            // Even if logging fails, still logout the user
            $this->security->destroySession();
            $this->redirect('/login');
        }
    }
    
    /**
     * Display user profile
     */
    public function profile()
    {
        try {
            $this->requireAuth();
            
            $userModel = new User();
            $user = $userModel->find($_SESSION['user_id']);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            $this->view('auth/profile', [
                'title' => 'الملف الشخصي',
                'user' => $user,
                'csrf_token' => $this->generateCSRFToken()
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e, 'Profile loading failed');
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile()
    {
        try {
            $this->requireAuth();
            $this->validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $userModel = new User();
            
            // Get form data
            $data = [
                'full_name' => $this->getPost('full_name'),
                'email' => $this->getPost('email'),
                'phone' => $this->getPost('phone')
            ];
            
            // Validate required fields
            $this->validateRequired($data, ['full_name', 'email']);
            
            // Validate email format
            if (!$this->validateEmail($data['email'])) {
                throw new Exception('Invalid email format');
            }
            
            // Check if email is already taken by another user
            $existingUser = $userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                throw new Exception('Email address is already in use');
            }
            
            // Update user profile
            $userModel->update($userId, $data);
            
            // Update session data
            $_SESSION['user_name'] = $data['full_name'];
            
            // Log activity
            $this->logActivity('profile_updated', 'User profile updated', [
                'user_id' => $userId,
                'updated_fields' => array_keys($data)
            ]);
            
            $this->json(['success' => true, 'message' => 'Profile updated successfully']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword()
    {
        try {
            $this->requireAuth();
            $this->validateCSRF();
            
            $userId = $_SESSION['user_id'];
            $userModel = new User();
            
            // Get form data
            $currentPassword = $this->getPost('current_password');
            $newPassword = $this->getPost('new_password');
            $confirmPassword = $this->getPost('confirm_password');
            
            // Validate required fields
            $this->validateRequired([
                'current_password' => $currentPassword,
                'new_password' => $newPassword,
                'confirm_password' => $confirmPassword
            ], ['current_password', 'new_password', 'confirm_password']);
            
            // Get current user
            $user = $userModel->find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Verify current password
            if (!$this->security->verifyPassword($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Check if new password matches confirmation
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New password and confirmation do not match');
            }
            
            // Validate password strength
            $passwordErrors = $this->security->validatePasswordStrength($newPassword);
            if (!empty($passwordErrors)) {
                throw new Exception('Password does not meet security requirements: ' . implode(', ', $passwordErrors));
            }
            
            // Hash and update password
            $hashedPassword = $this->security->hashPassword($newPassword);
            $userModel->changePassword($userId, $hashedPassword);
            
            // Log activity
            $this->logActivity('password_changed', 'User password changed', [
                'user_id' => $userId
            ]);
            
            // Log security event
            $this->security->logSecurityEvent('password_changed', 'Password changed successfully', [
                'user_id' => $userId
            ]);
            
            $this->json(['success' => true, 'message' => 'Password changed successfully']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Check if user is authenticated (AJAX endpoint)
     */
    public function checkAuth()
    {
        if (isset($_SESSION['user_id'])) {
            $this->json([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'role' => $_SESSION['user_role']
                ]
            ]);
        } else {
            $this->json(['authenticated' => false], 401);
        }
    }
}