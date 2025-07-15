<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "❌ طريقة الطلب غير مسموحة"]);
    exit;
}

$table = $_GET['table'] ?? '';
$unsynced = $_GET['unsynced'] ?? '0';

if ($table !== 'clients') {
    http_response_code(400);
    echo json_encode(["error" => "❌ جدول غير مدعوم"]);
    exit;
}

$where = ($unsynced === '1') ? "WHERE synced = 0" : "";
$sql = "SELECT id, code, name, phone, password, balance, start_date, insurance_balance, synced FROM clients $where";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "❌ خطأ في الاستعلام", "details" => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
