<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../config.php');

// تحديد client_id سواءً مباشرة أو عبر code
if (isset($_GET['client_id'])) {
    $client_id = intval($_GET['client_id']);
} elseif (isset($_GET['code'])) {
    $code = $_GET['code'];

    // استعلام للحصول على id من clients باستخدام code
    $stmtCode = $conn->prepare("SELECT id FROM clients WHERE code = ?");
    $stmtCode->bind_param("s", $code);
    $stmtCode->execute();
    $resultCode = $stmtCode->get_result();

    if ($row = $resultCode->fetch_assoc()) {
        $client_id = intval($row['id']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'العميل غير موجود']);
        exit;
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'معرف العميل غير موجود']);
    exit;
}

// الآن عندنا client_id جاهز

// التعديل: جلب المطالبات المفتوحة والجزئية معًا
$query = "SELECT id, description, amount, paid_amount, created_at 
          FROM transactions 
          WHERE client_id = ? 
            AND type = 'مطالبة' 
            AND amount > paid_amount 
           AND status IN ('open', 'partial')
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'خطأ في تجهيز الاستعلام: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();

$claims = [];

while ($row = $result->fetch_assoc()) {
    // حساب المبلغ المتبقي
    $remaining = $row['amount'] - $row['paid_amount'];
    $row['remaining_amount'] = $remaining;
    $claims[] = $row;
}

echo json_encode(['status' => 'success', 'claims' => $claims]);