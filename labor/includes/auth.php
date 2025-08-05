<?php
// Simple admin authentication - temporary fix
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Basic check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Update last activity
$_SESSION['last_activity'] = time();
?>