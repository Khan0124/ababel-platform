<?php
require_once '../bootstrap.php';

// Require authentication
$user = require_lab_auth();

try {
    // Logout user
    $authService->logout($user['id'], $_SESSION['session_token'], $user['type']);
    
    // Clear all session data
    session_destroy();
    
    // Set success message in session for next page
    session_start();
    flash('success', 'تم تسجيل الخروج بنجاح');
    
    // Redirect to login page
    redirect('login.php');
    
} catch (Exception $e) {
    // Log error
    error_log('Logout error: ' . $e->getMessage());
    
    // Clear session anyway
    session_destroy();
    
    // Redirect to login page
    redirect('login.php');
}
?> 