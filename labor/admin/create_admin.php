<?php
// إنشاء حساب مشرف جديد
session_start();
include '../includes/config.php';
include '../includes/session_manager.php';

$security = new SecurityManager($conn);

// التحقق من وجود مشرف مسجل دخول (اختياري - يمكن إزالته للسماح بإنشاء المشرف الأول)
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit;
// }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // التحقق من CSRF Token
        if (!isset($_POST['csrf_token']) || !$security->verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception('رمز الأمان غير صحيح');
        }
        
        $name = $security->sanitizeInput($_POST['name']);
        $email = $security->sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // التحقق من المدخلات
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception('يرجى ملء جميع الحقول المطلوبة');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('صيغة البريد الإلكتروني غير صحيحة');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('كلمتا المرور غير متطابقتين');
        }
        
        // التحقق من قوة كلمة المرور
        $password_validation = $security->validatePasswordStrength($password);
        if ($password_validation !== true) {
            throw new Exception(implode('<br>', $password_validation));
        }
        
        // التحقق من عدم وجود المشرف مسبقاً
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('البريد الإلكتروني مستخدم بالفعل');
        }
        $stmt->close();
        
        // تشفير كلمة المرور وإنشاء المشرف
        $hashed_password = $security->hashPassword($password);
        
        $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $success = 'تم إنشاء حساب المشرف بنجاح';
            // إعادة توجيه بعد 2 ثانية
            header("refresh:2;url=login.php");
        } else {
            throw new Exception('حدث خطأ أثناء إنشاء الحساب');
        }
        
        $stmt->close();
        
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
    <title>إنشاء حساب مشرف</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/auth-modern.css">
    <style>
        .auth-logo {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .password-requirements ul {
            margin: 0;
            padding-right: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">+</div>
                    <h1 class="auth-title">إنشاء حساب مشرف</h1>
                    <p class="auth-subtitle">قم بملء البيانات لإنشاء حساب جديد</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?= $error ?>
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
                        <label for="name" class="form-label">الاسم الكامل</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <input type="text" id="name" name="name" class="form-input" 
                                   placeholder="أدخل الاسم الكامل" 
                                   value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <input type="email" id="email" name="email" class="form-input" 
                                   placeholder="admin@example.com" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="أدخل كلمة مرور قوية" 
                                   required>
                        </div>
                        <div class="password-requirements">
                            يجب أن تحتوي كلمة المرور على:
                            <ul>
                                <li>8 أحرف على الأقل</li>
                                <li>حرف كبير وحرف صغير</li>
                                <li>رقم واحد على الأقل</li>
                                <li>رمز خاص واحد على الأقل</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                                   placeholder="أعد إدخال كلمة المرور" 
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        إنشاء الحساب
                    </button>
                </form>
                
                <div class="auth-links">
                    <a href="login.php" class="auth-link">لديك حساب بالفعل؟ تسجيل الدخول</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>