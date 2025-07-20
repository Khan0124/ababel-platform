<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
  die("معرّف غير صالح.");
}

// جلب تفاصيل المعاملة
$tx = $conn->query("SELECT * FROM transactions WHERE id = $id")->fetch_assoc();
if (!$tx) {
  die("لم يتم العثور على المعاملة.");
}

$client_id = $tx['client_id'];
$amount = floatval($tx['amount']);
$type = $tx['type'];
$description = $tx['description'];

// حذف من cashbox حسب transaction_id أو المصدر والوصف
$conn->query("DELETE FROM cashbox WHERE transaction_id = $id");

if ($conn->affected_rows === 0 && $description === 'تأمين' && $type === 'قبض') {
    $conn->query("DELETE FROM cashbox 
        WHERE source = 'رصيد التأمين' 
        AND description = 'تأمين' 
        AND amount = $amount 
        AND client_id = $client_id 
        LIMIT 1");
}

// حذف من transactions
$conn->query("DELETE FROM transactions WHERE id = $id");

// تعديل رصيد العميل أو رصيد التأمين
if ($type === 'مطالبة') {
  $conn->query("UPDATE clients SET balance = balance - $amount WHERE id = $client_id");
} elseif ($type === 'قبض' && $description !== 'تأمين') {
  $conn->query("UPDATE clients SET balance = balance + $amount WHERE id = $client_id");
} elseif ($description === 'تأمين' && $type === 'قبض') {
  $conn->query("UPDATE clients SET insurance_balance = insurance_balance - $amount WHERE id = $client_id");
}

header("Location: receipts_list.php");
exit;
?>