<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM labs WHERE id = $id");
}
header("Location: /admin/labs");
exit;