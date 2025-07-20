<?php
include 'config.php';
$code = $_GET['code'] ?? '';
$stmt = $conn->prepare("SELECT id, name FROM clients WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());
?>
