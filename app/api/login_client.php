<?php
header('Content-Type: application/json');
require_once '../config.php';

$code = $_POST['code'] ?? '';
$password = $_POST['password'] ?? '';

if (!$code || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات ناقصة']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM clients WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'العميل غير موجود']);
    exit;
}

$client = $result->fetch_assoc();
if (!password_verify($password, $client['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'كلمة المرور غير صحيحة']);
    exit;
}

// استرجاع الحاويات والعمليات (نفس الطريقة السابقة)
$containers = [];
$c_stmt = $conn->prepare("SELECT * FROM containers WHERE code = ?");
$c_stmt->bind_param("s", $code);
$c_stmt->execute();
$c_res = $c_stmt->get_result();
while ($row = $c_res->fetch_assoc()) {
    $containers[] = [
        'id' => $row['id'],
        'container_number' => $row['container_number'],
        'entry_date' => $row['entry_date'],
        'release_status' => $row['release_status']
    ];
}

$transactions = [];
$t_stmt = $conn->prepare("SELECT * FROM cashbox WHERE client_id = ? ORDER BY created_at DESC LIMIT 10");
$t_stmt->bind_param("i", $client['id']);
$t_stmt->execute();
$t_res = $t_stmt->get_result();
while ($row = $t_res->fetch_assoc()) {
    $transactions[] = [
        'id' => $row['id'],
        'type' => $row['type'],
        'amount' => $row['amount'],
        'created_at' => $row['created_at'],
        'description' => $row['description']
    ];
}

echo json_encode([
    'status' => 'success',
    'client' => [
        'id' => $client['id'],
        'name' => $client['name'],
        'code' => $client['code'],
        'balance' => $client['balance'],
        'insurance_balance' => $client['insurance_balance'],
        'phone' => $client['phone'],
        'start_date' => $client['start_date']
    ],
    'containers' => $containers,
    'transactions' => $transactions
]);
?>