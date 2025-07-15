
<?php
header('Content-Type: application/json');
require_once '../config.php';

$code = $_GET['code'] ?? '';
if (!$code) {
    echo json_encode(['status' => 'error', 'message' => 'كود ناقص']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM clients WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$client_result = $stmt->get_result();

if ($client_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'العميل غير موجود']);
    exit;
}
$client = $client_result->fetch_assoc();

// استرجاع الحاويات
$containers = [];
$c_stmt = $conn->prepare("SELECT * FROM containers WHERE code = ?");
$c_stmt->bind_param("s", $code);
$c_stmt->execute();
$c_res = $c_stmt->get_result();
while ($row = $c_res->fetch_assoc()) {
    $containers[] = $row;
}

// استرجاع العمليات
$transactions = [];
$t_stmt = $conn->prepare("SELECT * FROM cashbox WHERE client_code = ? ORDER BY date DESC");
$t_stmt->bind_param("s", $code);
$t_stmt->execute();
$t_res = $t_stmt->get_result();
while ($row = $t_res->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode([
    'status' => 'success',
    'client' => [
        'name' => $client['name'],
        'code' => $client['code'],
        'balance' => $client['balance'],
        'insurance' => $client['insurance_balance'],
        'containers' => $containers,
        'transactions' => $transactions
    ]
]);
?>
