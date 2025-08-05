<?php
/**
 * Secure Employee Authentication Check
 * Include this file at the top of all employee-restricted pages
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/session_manager.php';

// Initialize managers
$sessionManager = new SessionManager($conn);
$security = new SecurityManager($conn);

// Check if employee is logged in
if (!isset($_SESSION['employee_id']) || !isset($_SESSION['lab_id'])) {
    // Log unauthorized access attempt
    $security->logSecurityEvent('unauthorized_access', 'Employee not logged in', $_SERVER['REMOTE_ADDR'] ?? '');
    
    // Redirect to login
    header('Location: lab_login.php?error=unauthorized');
    exit;
}

// Validate session
if (!$sessionManager->validateSession()) {
    // Log invalid session
    $security->logSecurityEvent('invalid_session', 'Employee session validation failed', $_SERVER['REMOTE_ADDR'] ?? '');
    
    // Clear session and redirect
    $sessionManager->logout();
    header('Location: lab_login.php?error=session_expired');
    exit;
}

// Verify lab is still active
$stmt = $conn->prepare("SELECT l.status, le.status as employee_status, le.role 
                       FROM lab_employees le 
                       JOIN labs l ON le.lab_id = l.id 
                       WHERE le.id = ? AND l.id = ?");
$stmt->bind_param("ii", $_SESSION['employee_id'], $_SESSION['lab_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Employee or lab not found
    $security->logSecurityEvent('invalid_employee', 'Employee or lab not found', $_SERVER['REMOTE_ADDR'] ?? '');
    $sessionManager->logout();
    header('Location: lab_login.php?error=unauthorized');
    exit;
}

$auth_check = $result->fetch_assoc();
$stmt->close();

// Check lab status
if ($auth_check['status'] !== 'active') {
    $security->logSecurityEvent('inactive_lab', 'Lab is inactive', $_SERVER['REMOTE_ADDR'] ?? '');
    $sessionManager->logout();
    header('Location: lab_login.php?error=lab_inactive');
    exit;
}

// Check employee status
if ($auth_check['employee_status'] !== 'نشط' && $auth_check['employee_status'] !== 'active') {
    $security->logSecurityEvent('inactive_employee', 'Employee is inactive', $_SERVER['REMOTE_ADDR'] ?? '');
    $sessionManager->logout();
    header('Location: lab_login.php?error=account_disabled');
    exit;
}

// Update session with current role
$_SESSION['employee_role'] = $auth_check['role'];

// Set security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = $security->generateCSRFToken();
}

// Helper function to check employee permissions
function hasPermission($permission) {
    $permissions = [
        'مدير' => ['all'],
        'طبيب' => ['patients', 'exams', 'results', 'reports'],
        'محضر' => ['patients', 'exams', 'stock'],
        'محاسب' => ['cashbox', 'reports', 'invoices']
    ];
    
    $role = $_SESSION['employee_role'] ?? '';
    
    // Manager has all permissions
    if ($role === 'مدير' || in_array('all', $permissions[$role] ?? [])) {
        return true;
    }
    
    // Check specific permission
    return in_array($permission, $permissions[$role] ?? []);
}

// Helper function to validate CSRF token
function validateCSRF($token) {
    global $security;
    
    if (!isset($_SESSION['csrf_token']) || !$security->verifyCSRFToken($token)) {
        $security->logSecurityEvent('csrf_failure', 'CSRF token validation failed', $_SERVER['REMOTE_ADDR'] ?? '');
        die('Security error: Invalid request token');
    }
    
    return true;
}

// Update last activity
$_SESSION['last_activity'] = time();
?>