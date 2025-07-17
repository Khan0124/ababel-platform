<?php
header('Content-Type: application/json');
require_once '../config.php';

$code = $_POST['code'] ?? '';
$amount = $_POST['amount'] ?? 0;
$method = $_POST['method'] ?? '';
$note = $_POST['note'] ?? '';
$transfer = $_POST['transfer_number'] ?? '';
$image = $_FILES['image'] ?? null;
$related_claim_id = $_POST['related_claim_id'] ?? 0;

if (!$code || !$amount || !$method || !$related_claim_id) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات ناقصة']);
    exit;
}

// جلب client_id من clients عبر code
$stmt = $conn->prepare("SELECT id FROM clients WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'العميل غير موجود']);
    exit;
}

$client = $result->fetch_assoc();
$client_id = $client['id'];

// معالجة الصورة
$image_path = '';
if ($image && isset($image['tmp_name']) && $image['tmp_name']) {
    $folder = '../uploads/receipts/';
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    $filename = time() . '_' . basename($image['name']);
    $target = $folder . $filename;
    if (move_uploaded_file($image['tmp_name'], $target)) {
        $image_path = 'uploads/receipts/' . $filename;
    }
}

// توليد رقم سريالي للمعاملة (YYYYMMDD-ID)
$date_prefix = date('Ymd');
$serial = $date_prefix . '-' . uniqid();

// تسجيل العملية في transactions كـ "قبض" pending
$stmt = $conn->prepare("INSERT INTO transactions (
    client_id, 
    type, 
    amount, 
    description, 
    created_at, 
    payment_method, 
    reference_number, 
    receipt_image, 
    approval_status, 
    status, 
    paid_amount,
    serial,
    related_claim_id
) VALUES (?, 'قبض', ?, ?, NOW(), ?, ?, ?, 'pending', 'open', 0, ?, ?)");

$stmt->bind_param("idsssssi", 
    $client_id, 
    $amount, 
    $note, 
    $method, 
    $transfer, 
    $image_path,
    $serial,
    $related_claim_id
);

$exec = $stmt->execute();

if (!$exec) {
    error_log("فشل في تسجيل الطلب: " . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'فشل في تسجيل الطلب. يرجى المحاولة لاحقاً.']);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'تم إرسال طلب السداد. بانتظار المراجعة.']);
?>