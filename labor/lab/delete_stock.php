<?php
session_start();
include '../includes/auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM stock_items WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $lab_id);
$stmt->execute();

header("Location: stock_list.php");
exit;
