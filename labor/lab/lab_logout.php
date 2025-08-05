<?php
// تسجيل خروج آمن للموظفين
session_start();
include '../includes/config.php';
include '../includes/session_manager.php';

$sessionManager = new SessionManager($conn);

// تسجيل خروج آمن
$sessionManager->logout();

// إعادة التوجيه مع رسالة نجاح
header("Location: lab_login.php?success=logout");
exit;