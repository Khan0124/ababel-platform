<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

include 'config.php'; // الاتصال بقاعدة البيانات

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "❌ طريقة الطلب غير مسموحة"]);
    exit;
}

$table = $_GET['table'] ?? '';
if ($table !== 'clients') {
    http_response_code(400);
    echo json_encode(["error" => "❌ جدول غير مدعوم"]);
    exit;
}

// استقبال البيانات من POST بصيغة JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "❌ بيانات غير صالحة"]);
    exit;
}

$inserted = 0;
$skipped = 0;
$errors = [];

foreach ($data as $row) {
    $code = $row['code'] ?? '';
    $name = $row['name'] ?? '';
    $phone = $row['phone'] ?? '';
    $password = $row['password'] ?? '';
    $balance = $row['balance'] ?? 0;
    $start_date = $row['start_date'] ?? date('Y-m-d');
    $insurance = $row['insurance_balance'] ?? 0;
    $synced = 1;

    if (!$code || !$name || !$phone || !$password) {
        $skipped++;
        continue;
    }

    // التحقق من التكرار عبر الكود
    $stmt = $conn->prepare("SELECT id FROM clients WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $skipped++;
        continue;
    }

    // الإدخال
    $stmt = $conn->prepare("INSERT INTO clients (code, name, phone, password, balance, start_date, insurance_balance, synced)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $errors[] = "خطأ في التحضير: " . $conn->error;
        $skipped++;
        continue;
    }

    $stmt->bind_param("ssssdsdi", $code, $name, $phone, $password, $balance, $start_date, $insurance, $synced);
    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "فشل في إدخال العميل $code: " . $stmt->error;
        $skipped++;
    }
}

echo json_encode([
    "status" => "success",
    "inserted" => $inserted,
    "skipped" => $skipped,
    "errors" => $errors
], JSON_UNESCAPED_UNICODE);
