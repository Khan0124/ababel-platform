<?php
// تسجيل دخول آمن للموظفين
session_start();
include '../includes/config.php';
include '../includes/session_manager.php';

// إعادة التوجيه إذا كان مسجل دخول بالفعل
if (isset($_SESSION['employee_id'])) {
    header("Location: lab_dashboard.php");
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
        case 'lab_inactive':
            $error = 'المختبر غير مفعل حالياً';
            break;
        case 'unauthorized':
            $error = 'غير مصرح لك بالوصول';
            break;
    }
}

if (isset($_GET['success']) && $_GET['success'] === 'logout') {
    $success = 'تم تسجيل الخروج بنجاح';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // التحقق من CSRF Token
        if (!isset($_POST['csrf_token']) || !$security->verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception('رمز الأمان غير صحيح');
        }
        
        $username = $security->sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        // التحقق من المدخلات
        if (empty($username) || empty($password)) {
            throw new Exception('يرجى ملء جميع الحقول المطلوبة');
        }
        
        // محاولة تسجيل الدخول
        $login_result = $sessionManager->employeeLogin($username, $password);
        
        if ($login_result['success']) {
            header("Location: lab_dashboard.php");
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
    <title>تسجيل دخول موظف المختبر</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/auth-modern.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">م</div>
                    <h1 class="auth-title">تسجيل الدخول</h1>
                    <p class="auth-subtitle">مرحباً بك في نظام إدارة المختبر</p>
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
                        <label for="username" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <input type="text" id="username" name="username" class="form-input" 
                                   placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" 
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