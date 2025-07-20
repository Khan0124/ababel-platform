<?php
// التحقق من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// دالة للتحقق من الصلاحيات
function hasPermission($permission) {
    $allowedRoles = [
        'view_dashboard' => ['مدير عام', 'محاسب', 'مدير مكتب'],
        'manage_clients' => ['مدير عام', 'محاسب'],
        // إضافة المزيد من الصلاحيات حسب الحاجة
    ];

    $userRole = $_SESSION['role'] ?? '';
    
    if (isset($allowedRoles[$permission])) {
        return in_array($userRole, $allowedRoles[$permission]);
    }
    
    return false;
}