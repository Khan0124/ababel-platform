<?php
// إضافة موظف جديد
include 'auth_check.php';
include '../includes/config.php';
include '../includes/session_manager.php';

requireEmployeeRole(['مدير']);

$security = new SecurityManager($conn);
$lab_id = $_SESSION['lab_id'];
$shifts = $conn->query("SELECT id, name FROM shifts WHERE lab_id = $lab_id");

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
        $username = $security->sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $security->sanitizeInput($_POST['role']);
        $status = $security->sanitizeInput($_POST['status']);
        $shift_id = intval($_POST['shift_id']);
        
        // التحقق من المدخلات
        if (empty($name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
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
        
        // التحقق من عدم وجود الموظف مسبقاً
        $stmt = $conn->prepare("SELECT id FROM lab_employees WHERE (email = ? OR username = ?) AND lab_id = ?");
        $stmt->bind_param("ssi", $email, $username, $lab_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('البريد الإلكتروني أو اسم المستخدم مستخدم بالفعل');
        }
        $stmt->close();
        
        // تشفير كلمة المرور وإنشاء الموظف
        $hashed_password = $security->hashPassword($password);
        
        $stmt = $conn->prepare("INSERT INTO lab_employees (lab_id, name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $lab_id, $name, $email, $username, $hashed_password, $role, $status);
        
        if ($stmt->execute()) {
            $employee_id = $conn->insert_id;
            
            // إضافة الموظف للوردية
            if ($shift_id > 0) {
                $conn->query("INSERT INTO employee_shifts (employee_id, shift_id) VALUES ($employee_id, $shift_id)");
            }
            
            $success = 'تم إضافة الموظف بنجاح';
            // إعادة توجيه بعد 2 ثانية
            header("refresh:2;url=employees_list.php");
        } else {
            throw new Exception('حدث خطأ أثناء إضافة الموظف');
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
    <title>إضافة موظف جديد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/auth-modern.css">
    <style>
        body {
            background-color: #F9FAFB;
        }
        .page-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--primary-hover);
        }
        .form-header {
            margin-bottom: 2rem;
        }
        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .form-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <a href="employees_list.php" class="back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            العودة لقائمة الموظفين
        </a>
        
        <div class="form-header">
            <h1 class="form-title">إضافة موظف جديد</h1>
            <p class="form-subtitle">قم بملء البيانات لإضافة موظف جديد للمختبر</p>
        </div>
        
        <div class="form-card">
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
                    <input type="text" id="name" name="name" class="form-input" 
                           placeholder="أدخل الاسم الكامل للموظف" 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                           required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               placeholder="employee@example.com" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text" id="username" name="username" class="form-input" 
                               placeholder="اسم مستخدم فريد" 
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="كلمة مرور قوية" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="أعد إدخال كلمة المرور" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role" class="form-label">الدور الوظيفي</label>
                        <select name="role" id="role" class="form-input" required>
                            <option value="">اختر الدور</option>
                            <option value="محضر" <?= isset($_POST['role']) && $_POST['role'] == 'محضر' ? 'selected' : '' ?>>محضر</option>
                            <option value="طبيب" <?= isset($_POST['role']) && $_POST['role'] == 'طبيب' ? 'selected' : '' ?>>طبيب مختبر</option>
                            <option value="محاسب" <?= isset($_POST['role']) && $_POST['role'] == 'محاسب' ? 'selected' : '' ?>>محاسب</option>
                            <option value="مدير" <?= isset($_POST['role']) && $_POST['role'] == 'مدير' ? 'selected' : '' ?>>مدير</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">الحالة</label>
                        <select name="status" id="status" class="form-input" required>
                            <option value="نشط" <?= isset($_POST['status']) && $_POST['status'] == 'نشط' ? 'selected' : '' ?>>نشط</option>
                            <option value="معطل" <?= isset($_POST['status']) && $_POST['status'] == 'معطل' ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="shift_id" class="form-label">الوردية</label>
                    <select name="shift_id" id="shift_id" class="form-input">
                        <option value="0">بدون وردية</option>
                        <?php while($shift = $shifts->fetch_assoc()): ?>
                        <option value="<?= $shift['id'] ?>"><?= htmlspecialchars($shift['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">
                    إضافة الموظف
                </button>
            </form>
        </div>
    </div>
</body>
</html>