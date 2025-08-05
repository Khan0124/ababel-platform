<?php
/**
 * نظام مصادقة موحد وبسيط للمشرفين
 * يتم تضمين هذا الملف في جميع صفحات المشرفين
 */

// بدء الجلسة إذا لم تكن مبدأة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// التحقق الأساسي من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php?error=unauthorized');
    exit;
}

// التحقق من انتهاء الجلسة (30 دقيقة)
if (isset($_SESSION['login_time'])) {
    $time_since_login = time() - $_SESSION['login_time'];
    if ($time_since_login > 1800) { // 30 دقيقة
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit;
    }
}

// تحديث وقت آخر نشاط
$_SESSION['last_activity'] = time();
?>