<?php
include 'auth.php';
include 'config.php';

header('Content-Type: application/json');

$newCount = 0;
if ($_SESSION['office'] === 'بورتسودان') {
    $query = $conn->query("SELECT COUNT(*) as count FROM containers WHERE china_loading_id IS NOT NULL AND seen_by_port = 0");
    if ($query) {
        $result = $query->fetch_assoc();
        $newCount = intval($result['count']);
    }
}

echo json_encode(['new_containers' => $newCount]);
$conn->close();
?>