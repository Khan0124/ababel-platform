<?php
/**
 * نظام مصادقة موحد وبسيط للموظفين
 * يتم تضمين هذا الملف في جميع صفحات الموظفين
 */

// بدء الجلسة إذا لم تكن مبدأة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// التحقق الأساسي من تسجيل الدخول
if (!isset($_SESSION['employee_id']) || !isset($_SESSION['lab_id'])) {
    header('Location: lab_login.php?error=unauthorized');
    exit;
}

// التحقق من انتهاء الجلسة (30 دقيقة)
if (isset($_SESSION['login_time'])) {
    $time_since_login = time() - $_SESSION['login_time'];
    if ($time_since_login > 1800) { // 30 دقيقة
        session_destroy();
        header('Location: lab_login.php?error=session_expired');
        exit;
    }
}

// تحديث وقت آخر نشاط
$_SESSION['last_activity'] = time();

// التأكد من وجود role في الجلسة
if (!isset($_SESSION['employee_role'])) {
    // جلب الدور من قاعدة البيانات إذا لم يكن موجوداً
    include_once dirname(__DIR__) . '/includes/config.php';
    $stmt = $conn->prepare("SELECT role FROM lab_employees WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['employee_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['employee_role'] = $row['role'];
    } else {
        $_SESSION['employee_role'] = 'موظف'; // قيمة افتراضية
    }
    $stmt->close();
}

// دالة للتحقق من صلاحيات الموظف
function hasPermission($permission) {
    $permissions = [
        'مدير' => ['all'],
        'طبيب' => ['patients', 'exams', 'results', 'reports'],
        'محضر' => ['patients', 'exams', 'stock'],
        'محاسب' => ['cashbox', 'reports', 'invoices']
    ];
    
    $role = $_SESSION['employee_role'] ?? '';
    
    // المدير له جميع الصلاحيات
    if ($role === 'مدير' || in_array('all', $permissions[$role] ?? [])) {
        return true;
    }
    
    // التحقق من صلاحية محددة
    return in_array($permission, $permissions[$role] ?? []);
}

// دالة للتحقق من الأدوار المطلوبة
function requireEmployeeRole($allowed_roles) {
    $user_role = $_SESSION['employee_role'] ?? '';
    
    if (!in_array($user_role, $allowed_roles)) {
        die('
        <!DOCTYPE html>
        <html dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>غير مصرح</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .error-box {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .error-icon {
                    font-size: 60px;
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                h2 {
                    color: #333;
                    margin-bottom: 10px;
                }
                p {
                    color: #666;
                    margin-bottom: 30px;
                }
                a {
                    background: #007bff;
                    color: white;
                    padding: 10px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    display: inline-block;
                }
                a:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-icon">⚠️</div>
                <h2>غير مصرح لك بالوصول</h2>
                <p>عذراً، ليس لديك الصلاحيات الكافية للوصول لهذه الصفحة.</p>
                <a href="lab_dashboard.php">العودة للوحة التحكم</a>
            </div>
        </body>
        </html>
        ');
        exit;
    }
}
?>