<?php
// ababel/app/check_new_containers.php
include 'auth.php';
include 'config.php';

header('Content-Type: application/json');

$response = ['new_containers' => 0];

if ($_SESSION['office'] === 'بورتسودان') {
    $query = "SELECT COUNT(*) as count FROM containers 
              WHERE china_loading_id IS NOT NULL AND seen_by_port = 0";
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $response['new_containers'] = intval($row['count']);
    }
}

echo json_encode($response);