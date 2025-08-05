<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $lab = $conn->query("SELECT status FROM labs WHERE id = $id")->fetch_assoc();
    $newStatus = ($lab['status'] == 'active') ? 'inactive' : 'active';
    $conn->query("UPDATE labs SET status = '$newStatus' WHERE id = $id");
}
header("Location: labs_list.php");
exit;