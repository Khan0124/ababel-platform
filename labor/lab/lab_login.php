<?php
session_start();
include '../includes/config.php';
include '../includes/csrf.php';

$error = '';
$login_attempts = $_SESSION['lab_login_attempts'] ?? 0;
$last_attempt_time = $_SESSION['lab_last_attempt_time'] ?? 0;

// Rate limiting: Allow max 5 attempts per 15 minutes
if ($login_attempts >= 5 && (time() - $last_attempt_time) < 900) {
    $remaining_time = 900 - (time() - $last_attempt_time);
    $error = 'تم تجاوز عدد المحاولات المسموح. يرجى المحاولة بعد ' . ceil($remaining_time / 60) . ' دقيقة';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'خطأ في الأمان. يرجى المحاولة مرة أخرى';
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM lab_employees WHERE email = ? AND status = 'نشط' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['id'];
            $_SESSION['employee_name'] = $user['name'];
            $_SESSION['employee_role'] = $user['role'];
            $_SESSION['lab_id'] = $user['lab_id'];
            header("Location: /lab/dashboard");
            exit;
        } else {
            $error = "كلمة المرور غير صحيحة";
            $_SESSION['lab_login_attempts'] = ($login_attempts >= 5 && (time() - $last_attempt_time) >= 900) ? 1 : $login_attempts + 1;
            $_SESSION['lab_last_attempt_time'] = time();
        }
    } else {
        $error = "الحساب غير موجود أو غير نشط";
        $_SESSION['lab_login_attempts'] = ($login_attempts >= 5 && (time() - $last_attempt_time) >= 900) ? 1 : $login_attempts + 1;
        $_SESSION['lab_last_attempt_time'] = time();
    }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول موظف المعمل - نظام إدارة المعامل</title>
    <meta name="description" content="تسجيل دخول موظف المعمل لنظام إدارة المعامل">
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link href="/assets/login-style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
                    <i class="fas fa-flask"></i>
                </div>
                <h1 class="login-title">تسجيل دخول موظف المعمل</h1>
                <p class="login-subtitle">الوصول إلى لوحة تحكم المعمل</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post" id="loginForm" novalidate>
                <?= generateCSRFField() ?>
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        class="form-control" 
                        placeholder="example@domain.com"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    <div class="invalid-feedback" style="display: none;">
                        يرجى إدخال بريد إلكتروني صحيح
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="form-control" 
                            placeholder="أدخل كلمة المرور"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="إظهار كلمة المرور">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" style="display: none;">
                        يرجى إدخال كلمة المرور
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="form-checkbox">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <label for="remember">تذكرني</label>
                    </div>
                    <a href="#" class="forgot-link">نسيت كلمة المرور؟</a>
                </div>
                
                <button type="submit" class="btn-login" id="submitBtn" style="background: #27ae60; --primary-hover: #229954;">
                    <span id="btnText">تسجيل الدخول</span>
                    <span id="btnLoader" style="display: none;">
                        <span class="spinner"></span> جاري التحميل...
                    </span>
                </button>
            </form>
            
            <div class="divider">
                <span class="divider-text">أو</span>
            </div>
            
            <div class="login-footer">
                <p>مشرف النظام؟ <a href="/admin/login">تسجيل دخول المشرفين</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');
        
        // Email validation
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
                this.nextElementSibling.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                this.nextElementSibling.style.display = 'none';
            }
        });
        
        // Password validation
        passwordInput.addEventListener('blur', function() {
            if (this.value.length < 1) {
                this.classList.add('is-invalid');
                this.parentElement.nextElementSibling.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                this.parentElement.nextElementSibling.style.display = 'none';
            }
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            
            if (!emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                emailInput.nextElementSibling.style.display = 'block';
                isValid = false;
            }
            
            if (!passwordInput.value) {
                passwordInput.classList.add('is-invalid');
                passwordInput.parentElement.nextElementSibling.style.display = 'block';
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';
                
                // Submit form
                this.submit();
            } else {
                // Shake animation
                form.classList.add('shake');
                setTimeout(() => form.classList.remove('shake'), 500);
            }
        });
        
        // Remove invalid class on input
        [emailInput, passwordInput].forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                if (this.id === 'password') {
                    this.parentElement.nextElementSibling.style.display = 'none';
                } else {
                    this.nextElementSibling.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
