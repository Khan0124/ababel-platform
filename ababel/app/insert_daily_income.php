<?php
include 'auth.php';
include 'config.php';

$desc = $_POST['description'];
$amount = floatval($_POST['amount']);
$client_id = $_POST['client_id'] ?: null;
$user_id = $_SESSION['user_id'];
$date = date("Y-m-d H:i:s");

if ($amount <= 0) {
  die("⚠️ المبلغ يجب أن يكون أكبر من صفر");
}

$stmt = $conn->prepare("INSERT INTO cashbox (type, description, amount, client_id, user_id, created_at, source) VALUES ('قبض', ?, ?, ?, ?, ?, 'يومية قبض')");
$stmt->bind_param("sddis", $desc, $amount, $client_id, $user_id, $date);
$stmt->execute();

$receipt_id = $conn->insert_id;
header("Location: print_daily_income.php?id=$receipt_id");
exit;
?>
