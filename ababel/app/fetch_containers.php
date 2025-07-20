<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
include 'auth.php';

$result = $conn->query("SELECT * FROM containers ORDER BY id DESC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
?>