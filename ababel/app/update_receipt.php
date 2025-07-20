<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $type = $_POST['type'] ?? '';
  $description = $_POST['description'] ?? '';
  $amount = floatval($_POST['amount']);
  $payment_method = $_POST['payment_method'] ?? '';
  $container_id = intval($_POST['container_id']);
  $redirect_back = $_POST['redirect_back'] ?? "receipt_view.php?id=$id";

  if (!$id || !$type || !$description || $amount <= 0 || !$container_id) {
    die("⚠️ البيانات غير مكتملة أو غير صالحة.");
  }

  // جلب سعر الصرف الأصلي
  $receipt = $conn->query("SELECT exchange_rate FROM transactions WHERE id = $id")->fetch_assoc();
  $exchange_rate = $receipt['exchange_rate'] ?? 1;
  $amount_usd = $amount / $exchange_rate;

  // تعديل جدول المعاملات
  $stmt = $conn->prepare("UPDATE transactions SET 
    type = ?, 
    description = ?, 
    amount = ?, 
    amount_usd = ?, 
    payment_method = ?, 
    container_id = ? 
    WHERE id = ?");
  $stmt->bind_param("ssddsii", $type, $description, $amount, $amount_usd, $payment_method, $container_id, $id);
  $stmt->execute();

  // تعديل جدول الخزنة بناءً على transaction_id
  $update_cashbox = $conn->prepare("UPDATE cashbox SET amount = ? WHERE transaction_id = ?");
  $update_cashbox->bind_param("di", $amount, $id);
  $update_cashbox->execute();

  // الرجوع لنفس الصفحة
  header("Location: $redirect_back");
  exit;
} else {
  die("طريقة الطلب غير صحيحة.");
}
