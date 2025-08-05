<?php
// نظام المصادقة المحسن للموظفين
session_start();
require_once __DIR__ . '/../includes/session_manager.php';
require_once __DIR__ . '/../includes/config.php';

$sessionManager = new SessionManager($conn);

// التحقق من صحة الجلسة
if (!isset($_SESSION['employee_id']) || !isset($_SESSION['lab_id']) || !$sessionManager->validateSession()) {
    // تسجيل محاولة وصول غير مصرح بها
    $security = new SecurityManager($conn);
    $security->logSecurityEvent('unauthorized_access', 'Invalid employee session', $_SERVER['REMOTE_ADDR']);
    
    // إنهاء الجلسة وإعادة التوجيه
    $sessionManager->logout();
    header("Location: lab_login.php?error=session_expired");
    exit;
}

// التحقق من حالة المختبر
$stmt = $conn->prepare("SELECT status FROM labs WHERE id = ?");
$stmt->bind_param("i", $_SESSION['lab_id']);
$stmt->execute();
$result = $stmt->get_result();
$lab = $result->fetch_assoc();
$stmt->close();

if (!$lab || $lab['status'] !== 'active') {
    $security = new SecurityManager($conn);
    $security->logSecurityEvent('inactive_lab_access', 'Access attempt to inactive lab', $_SERVER['REMOTE_ADDR']);
    
    $sessionManager->logout();
    header("Location: lab_login.php?error=lab_inactive");
    exit;
}

// تحديث آخر نشاط
$_SESSION['last_activity'] = time();

// إضافة CSRF Token لجميع النماذج
if (!isset($_SESSION['csrf_token'])) {
    $security = new SecurityManager($conn);
    $_SESSION['csrf_token'] = $security->generateCSRFToken();
}
?>
