<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM lab_employees WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $lab_id);
$stmt->execute();

header("Location: employees_list.php");
exit;
