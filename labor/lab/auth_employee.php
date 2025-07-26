<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: lab_login.php");
    exit;
}
?>
