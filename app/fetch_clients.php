
<?php
include 'config.php';
include 'auth.php';
$res = $conn->query("SELECT * FROM clients ORDER BY id DESC");
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
header("Content-Type: application/json");
echo json_encode($data);
?>
