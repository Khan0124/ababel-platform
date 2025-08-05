<?php
// تسجيل خروج آمن للإدارة
session_start();
include '../includes/config.php';
include '../includes/session_manager.php';

$sessionManager = new SessionManager($conn);

// تسجيل خروج آمن
$sessionManager->logout();

// إعادة التوجيه مع رسالة نجاح
header("Location: login.php?success=logout");
exit;