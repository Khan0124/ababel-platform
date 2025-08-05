<?php
// تسجيل دخول آمن للإدارة
session_start();
include '../includes/config.php';
include '../includes/session_manager.php';

// إعادة التوجيه إذا كان مسجل دخول بالفعل
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$sessionManager = new SessionManager($conn);
$security = new SecurityManager($conn);

$error = '';
$success = '';

// معالجة رسائل النظام
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
            break;
        case 'unauthorized':
            $error = 'غير مصرح لك بالوصول';
            break;
    }
}

if (isset($_GET['success']) && $_GET['success'] === 'logout') {
    $success = 'تم تسجيل الخروج بنجاح';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // التحقق من CSRF Token
        if (!isset($_POST['csrf_token']) || !$security->verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception('رمز الأمان غير صحيح');
        }
        
        $email = $security->sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // التحقق من المدخلات
        if (empty($email) || empty($password)) {
            throw new Exception('يرجى ملء جميع الحقول المطلوبة');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('صيغة البريد الإلكتروني غير صحيحة');
        }
        
        // محاولة تسجيل الدخول
        $login_result = $sessionManager->adminLogin($email, $password);
        
        if ($login_result['success']) {
            header("Location: dashboard.php");
            exit;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// إنشاء CSRF Token للنموذج
$csrf_token = $security->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول المشرف</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/auth-modern.css">
    <style>
        .auth-logo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">أ</div>
                    <h1 class="auth-title">لوحة التحكم</h1>
                    <p class="auth-subtitle">تسجيل دخول المشرف</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <input type="email" id="email" name="email" class="form-input" 
                                   placeholder="admin@example.com" 
                                   required autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="أدخل كلمة المرور" 
                                   required autocomplete="current-password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        تسجيل الدخول
                    </button>
                </form>
                
                <div class="auth-links">
                    <a href="../" class="auth-link">العودة للصفحة الرئيسية</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>