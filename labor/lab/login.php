<?php
require_once '../bootstrap.php';

// Redirect if already authenticated
if (is_authenticated()) {
    redirect('lab_dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['_token']) || !$security->verifyCSRFToken($_POST['_token'])) {
            throw new Exception('رمز الأمان غير صحيح');
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            throw new Exception('يرجى إدخال البريد الإلكتروني وكلمة المرور');
        }
        
        // Authenticate lab
        $result = $authService->authenticateLab($email, $password);
        
        // Set session data
        $_SESSION['user_id'] = $result['lab_id'];
        $_SESSION['user_name'] = $result['lab_name'];
        $_SESSION['session_token'] = $result['session_token'];
        $_SESSION['user_type'] = 'lab';
        $_SESSION['lab_id'] = $result['lab_id'];
        $_SESSION['subscription_type'] = $result['subscription_type'];
        $_SESSION['subscription_end_date'] = $result['subscription_end_date'];
        
        // Store old input for form persistence
        $_SESSION['old'] = $_POST;
        
        // Redirect to dashboard
        redirect('lab_dashboard.php');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $_SESSION['old'] = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المختبرات</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/modern-style.css" rel="stylesheet">
    
    <style>
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        
        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #64748b;
            font-size: 1rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            color: #991b1b;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #bbf7d0, #86efac);
            color: #166534;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #e2e8f0;
            border-left: none;
            color: #64748b;
        }
        
        .form-control:focus + .input-group-text {
            border-color: #667eea;
        }
        
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <h1 class="login-title">تسجيل الدخول</h1>
                <p class="login-subtitle">نظام إدارة المختبرات الطبية</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?= csrf_field() ?>
                
                <div class="form-floating">
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="البريد الإلكتروني"
                           value="<?= old('email') ?>"
                           required>
                    <label for="email">البريد الإلكتروني</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="كلمة المرور"
                           required>
                    <label for="password">كلمة المرور</label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    تسجيل الدخول
                </button>
            </form>
            
            <div class="forgot-password">
                <a href="forgot-password.php">نسيت كلمة المرور؟</a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('يرجى إدخال جميع الحقول المطلوبة');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.btn-login');
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>جاري التحميل...';
            submitBtn.disabled = true;
        });
        
        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 