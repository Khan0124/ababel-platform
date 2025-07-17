<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$transaction_id = $_POST['transaction_id'] ?? null;
$approved_amount = $_POST['amount'] ?? 0;

if (!$transaction_id || $approved_amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات ناقصة']);
    exit;
}

// جلب معلومات عملية الدفع
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'المعاملة غير موجودة']);
    exit;
}
$payment = $res->fetch_assoc();

// جلب المطالبة الأصلية المرتبطة
$claim_id = $payment['related_claim_id'];
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->bind_param("i", $claim_id);
$stmt->execute();
$claim_res = $stmt->get_result();
if ($claim_res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'المطالبة الأصلية غير موجودة']);
    exit;
}
$claim = $claim_res->fetch_assoc();

$client_id = $claim['client_id'];
$claim_amount = $claim['amount'];
$paid_amount = $claim['paid_amount'];
$new_paid_amount = $paid_amount + $approved_amount;

// تحديد الحالة الجديدة
if ($new_paid_amount >= $claim_amount) {
    $new_status = 'paid';
    $new_paid_amount = $claim_amount;
} else {
    $new_status = 'partial';
}

// تحديث المطالبة الأصلية
$update = $conn->prepare("UPDATE transactions SET paid_amount = ?, status = ? WHERE id = ?");
$update->bind_param("dsi", $new_paid_amount, $new_status, $claim_id);
$update->execute();

// تحديث حالة عملية الدفع إلى "موافق عليها"
$update_payment = $conn->prepare("UPDATE transactions SET approval_status = 'approved' WHERE id = ?");
$update_payment->bind_param("i", $transaction_id);
$update_payment->execute();

// تسجيل العملية المقبوضة في الخزنة
$desc = "سداد مطالبة: " . $claim['description'];
$method = $payment['payment_method'];
$now = date("Y-m-d H:i:s");

$insert = $conn->prepare("INSERT INTO cashbox 
    (transaction_id, client_id, container_id, type, source, description, method, amount, created_at, notes) 
    VALUES (?, ?, ?, 'قبض', 'عميل', ?, ?, ?, ?, ?)");

$container_id = $claim['container_id'] ?? null;
$insert->bind_param("iiisssdss", 
    $transaction_id,
    $client_id,
    $container_id,
    $desc,
    $method,
    $approved_amount,
    $now,
    $desc
);
$insert->execute();

// تعديل رصيد العميل
$update_bal = $conn->prepare("UPDATE clients SET balance = balance - ? WHERE id = ?");
$update_bal->bind_param("di", $approved_amount, $client_id);
$update_bal->execute();

$message = [
    'to' => '/topics/client_' . $client_id,
    'notification' => [
        'title' => 'تمت الموافقة على الدفعة',
        'body' => 'المبلغ: ' . $approved_amount . ' - المطالبة: ' . $claim['description'],
    ],
    'data' => [
        'type' => 'payment_approved',
        'client_id' => $client_id,
    ]
];

echo json_encode(['status' => 'success', 'message' => 'تمت الموافقة على الدفع وتحديث البيانات بنجاح']);

// إرسال إشعار للعميل
require 'send_fcm.php';

// تحديث بيانات الرسالة
$message['message']['topic'] = '/topics/client_' . $client_id;
$message['message']['notification']['title'] = 'تمت الموافقة على الدفعة';
$message['message']['notification']['body'] = 'المبلغ: ' . $approved_amount . ' - المطالبة: ' . $claim['description'];
$message['message']['data'] = [
    'type' => 'payment_approved',
    'client_id' => $client_id
];

// إرسال الإشعار
$client = new Client();
$response = $client->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
    'headers' => [
        'Authorization' => "Bearer $accessToken",
        'Content-Type' => 'application/json',
    ],
    'json' => $message,
]);

echo json_encode(['status' => 'success', 'message' => 'تمت الموافقة على الدفع وتحديث البيانات بنجاح']);