<?php
include 'config.php';

$client_id = intval($_GET['id'] ?? 0);

$res = $conn->query("
  SELECT id, container_number
  FROM containers
  WHERE code = (SELECT code FROM clients WHERE id = $client_id LIMIT 1)
");

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);