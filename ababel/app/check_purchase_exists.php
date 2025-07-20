<?php
include 'config.php';

$loading = $_GET['loading_number'] ?? '';
$response = ['exists' => false];

if ($loading) {
  $stmt = $conn->prepare("SELECT id FROM purchase_expenses WHERE loading_number = ?");
  $stmt->bind_param("s", $loading);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $response['exists'] = true;
  }
}

header('Content-Type: application/json');
echo json_encode($response);
