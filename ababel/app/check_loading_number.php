<?php
include 'config.php';

$response = ['status' => 'error'];

if (!empty($_GET['loading_number'])) {
  $loading_number = $_GET['loading_number'];
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM register_requests WHERE loading_number = ?");
  $stmt->bind_param("s", $loading_number);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();

  if ($result['count'] > 0) {
    $response['status'] = 'exists';
  } else {
    $response['status'] = 'available';
  }
}

header('Content-Type: application/json');
echo json_encode($response);
