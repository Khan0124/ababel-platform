<?php
/**
 * User Login System
 * Enhanced version with modern PHP practices and security improvements
 */

declare(strict_types=1);

// Configuration and includes
require_once 'config.php';
require_once 'security.php';

// Constants
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_DURATION = 300; // 5 minutes
const REMEMBER_DURATION = 86400 * 30; // 30 days

class LoginHandler 
{
    private $conn;
    private $error = '';
    private $success = '';
    private $userData = []; // Fixed: Added property declaration
    
    public function __construct($database_connection) 
    {
        $this->conn = $database_connection;
    }
    
    /**
     * Handle login form submission
     */
    public function handleLogin(): void 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            $this->error = '⚠️ جلسة غير صالحة. يرجى المحاولة مرة أخرى.';
            return;
        }
        
        // Rate limiting check
        if ($this->isRateLimited()) {
            $this->error = '⚠️ تم تجاوز عدد محاولات تسجيل الدخول المسموح. يرجى المحاولة بعد قليل.';
            return;
        }
        
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validation
        if (empty($username) || empty($password)) {
            $this->error = '⚠️ جميع الحقول مطلوبة.';
            return;
        }
        
        if (!$this->validateCredentials($username, $password)) {
            $this->logFailedAttempt($username);
            $this->error = '⚠️ اسم المستخدم أو كلمة المرور غير صحيحة.';
            return;
        }
        
        // Login successful
        $this->processSuccessfulLogin($username, $remember);
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCsrfToken(): bool 
    {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check if user is rate limited
     */
    private function isRateLimited(): bool 
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "login_attempts_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
        }
        
        $attempts = $_SESSION[$key];
        
        // Reset if lockout period has passed
        if (time() - $attempts['last_attempt'] > LOCKOUT_DURATION) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
            return false;
        }
        
        return $attempts['count'] >= MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Log failed login attempt
     */
    private function logFailedAttempt(string $username): void 
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "login_attempts_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
        
        // Log to database for security monitoring
        $this->logSecurityEvent($username, $ip, 'failed_login');
    }
    
    /**
     * Log security events
     */
    private function logSecurityEvent(string $username, string $ip, string $event): void 
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO security_logs (username, ip_address, event_type, timestamp) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $username, $ip, $event);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Security logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Sanitize user input
     */
    private function sanitizeInput(string $input): string 
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Validate user credentials
     */
    private function validateCredentials(string $username, string $password): bool 
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, username, password, role, office FROM users WHERE username = ? AND status = 'مفعل' LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                return false;
            }
            
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password'])) {
                return false;
            }
            
            // Store user data for session creation
            $this->userData = $user;
            return true;
            
        } catch (Exception $e) {
            error_log("Database error during login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process successful login
     */
    private function processSuccessfulLogin(string $username, bool $remember): void 
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $this->userData['id'];
        $_SESSION['username'] = $this->userData['username'];
        $_SESSION['role'] = $this->userData['role'];
        $_SESSION['office'] = $this->userData['office'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Handle remember me functionality
        $this->handleRememberMe($username, $remember);
        
        // Log successful login
        $this->logSecurityEvent($username, $_SERVER['REMOTE_ADDR'], 'successful_login');
        
        // Clear failed attempts
        unset($_SESSION["login_attempts_{$_SERVER['REMOTE_ADDR']}"]);
        
        // Redirect to appropriate page
        $this->redirectAfterLogin();
    }
    
    /**
     * Handle remember me functionality
     */
    private function handleRememberMe(string $username, bool $remember): void 
    {
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + REMEMBER_DURATION;
            
            // Create variable for datetime string
            $expiresDate = date('Y-m-d H:i:s', $expires);
            
            // Store token in database
            $stmt = $this->conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires = ?");
            $stmt->bind_param("issss",  
                $this->userData['id'], 
                $token, 
                $expiresDate, 
                $token, 
                $expiresDate
            );
            $stmt->execute();
            
            // Set secure cookie
            setcookie("remember_token", $token, [
                'expires' => $expires,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        } else {
            // Clear remember token
            $this->clearRememberToken();
        }
    }
    
    /**
     * Clear remember token
     */
    private function clearRememberToken(): void 
    {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token']; // Create variable for binding
            $stmt = $this->conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
        }
        
        setcookie("remember_token", "", [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    /**
     * Redirect after successful login
     */
    private function redirectAfterLogin(): void 
    {
        $redirect_url = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
        unset($_SESSION['redirect_after_login']);
        
        header("Location: $redirect_url");
        exit;
    }
    
    /**
     * Get current error message
     */
    public function getError(): string 
    {
        return $this->error;
    }
    
    /**
     * Get success message
     */
    public function getSuccess(): string 
    {
        return $this->success;
    }
    
    /**
     * Check if user is already logged in
     */
    public function isLoggedIn(): bool 
    {
        return isset($_SESSION['user_id']);
    }
}

// Initialize CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Create login handler instance
$loginHandler = new LoginHandler($conn);

// Check if user is already logged in
if ($loginHandler->isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Handle login form submission
$loginHandler->handleLogin();

// Get saved username from remember token
$saved_username = '';
if (isset($_COOKIE['remember_token'])) {
    $stmt = $conn->prepare("SELECT u.username FROM users u JOIN remember_tokens r ON u.id = r.user_id WHERE r.token = ? AND r.expires > NOW()");
    $stmt->bind_param("s", $_COOKIE['remember_token']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $saved_username = $result->fetch_assoc()['username'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - أبابيل</title>
    <meta name="description" content="نظام تسجيل الدخول الآمن - أبابيل">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #711739;
            --primary-dark: #5c1130;
            --accent-color: #d4af37;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --warning-color: #f39c12;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --text-color: #2c3e50;
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                linear-gradient(45deg, transparent 40%, rgba(255, 255, 255, 0.02) 50%, transparent 60%);
            animation: bgAnimation 20s linear infinite;
            z-index: 0;
        }

        @keyframes bgAnimation {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            margin: 0 20px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1); }
        }

        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: var(--shadow-light);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Title */
        .title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-color);
            font-size: 28px;
            font-weight: 700;
            position: relative;
        }

        .title::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            margin: 10px auto;
            border-radius: 2px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            background: var(--light-gray);
            transition: var(--transition);
            font-family: 'Cairo', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(113, 23, 57, 0.1);
            transform: translateY(-2px);
        }

        .form-input:hover {
            border-color: var(--primary-color);
        }

        /* Remember Me Checkbox */
        .remember-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 10px;
        }

        .custom-checkbox {
            position: relative;
            width: 20px;
            height: 20px;
        }

        .custom-checkbox input {
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            background: var(--light-gray);
            border: 2px solid var(--border-color);
            border-radius: 4px;
            transition: var(--transition);
        }

        .checkmark::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            opacity: 0;
            transition: var(--transition);
        }

        .custom-checkbox input:checked + .checkmark {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .custom-checkbox input:checked + .checkmark::after {
            opacity: 1;
        }

        .remember-label {
            font-size: 14px;
            color: var(--text-color);
            cursor: pointer;
            user-select: none;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            font-family: 'Cairo', sans-serif;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(113, 23, 57, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1); }
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: var(--error-color);
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: var(--success-color);
        }

        /* Loading State */
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-box {
                padding: 30px 25px;
                margin: 0 15px;
            }
            
            .title {
                font-size: 24px;
            }
            
            .form-input {
                padding: 12px 16px;
                font-size: 14px;
            }
            
            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-box {
                background: rgba(30, 30, 30, 0.95);
                color: #f8f9fa;
            }
            
            .title {
                color: #f8f9fa;
            }
            
            .form-label {
                color: #f8f9fa;
            }
            
            .form-input {
                background: rgba(255, 255, 255, 0.1);
                color: #f8f9fa;
                border-color: rgba(255, 255, 255, 0.2);
            }
            
            .form-input:focus {
                background: rgba(255, 255, 255, 0.15);
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    
    <div class="particles">
        <?php for ($i = 0; $i < 20; $i++): ?>
            <div class="particle" style="left: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 15) ?>s; animation-duration: <?= rand(10, 20) ?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="logo.png" alt="شعار أبابيل" class="logo">
            </div>
            
            <h1 class="title">تسجيل الدخول</h1>
            
            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        value="<?= htmlspecialchars($saved_username) ?>" 
                        required 
                        autocomplete="username"
                        aria-describedby="username-error"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required 
                        autocomplete="current-password"
                        aria-describedby="password-error"
                    >
                </div>

                <div class="remember-container">
                    <div class="custom-checkbox">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            <?= $saved_username ? 'checked' : '' ?>
                        >
                        <span class="checkmark"></span>
                    </div>
                    <label for="remember" class="remember-label">تذكرني لمدة 30 يوماً</label>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    دخول
                </button>

                <?php if ($loginHandler->getError()): ?>
                    <div class="alert alert-error" role="alert">
                        <?= htmlspecialchars($loginHandler->getError()) ?>
                    </div>
                <?php endif; ?>

                <?php if ($loginHandler->getSuccess()): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($loginHandler->getSuccess()) ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Form enhancement and validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const inputs = form.querySelectorAll('.form-input');
            
            // Add loading state on form submission
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'جاري التحقق...';
            });
            
            // Real-time validation
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateInput(this);
                });
                
                input.addEventListener('blur', function() {
                    validateInput(this);
                });
            });
            
            function validateInput(input) {
                const value = input.value.trim();
                
                if (!value) {
                    input.style.borderColor = '#e74c3c';
                    return false;
                } else {
                    input.style.borderColor = '#2ecc71';
                    return true;
                }
            }
            
            // Auto-focus first empty input
            const firstEmpty = Array.from(inputs).find(input => !input.value.trim());
            if (firstEmpty) {
                firstEmpty.focus();
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>