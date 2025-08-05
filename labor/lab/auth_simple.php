<?php
// Simple authentication check without complex session validation
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Basic check if employee is logged in
if (!isset($_SESSION['employee_id']) || !isset($_SESSION['lab_id'])) {
    header('Location: lab_login.php');
    exit;
}

// Update last activity
$_SESSION['last_activity'] = time();

// Helper function for role checking
function requireEmployeeRole($allowed_roles) {
    $user_role = $_SESSION['employee_role'] ?? '';
    
    if (!in_array($user_role, $allowed_roles)) {
        die('غير مصرح لك بالوصول لهذه الصفحة');
    }
}
?>