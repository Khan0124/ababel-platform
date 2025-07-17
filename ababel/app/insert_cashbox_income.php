<?php
session_start();
include 'config.php';

$description = $_POST['description'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$method = $_POST['method'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if ($description && $amount > 0 && $method) {
  $stmt = $conn->prepare("INSERT INTO cashbox (type, source, description, amount, method, user_id, created_at) 
                          VALUES ('قبض', 'دخل خارجي', ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sdsi", $description, $amount, $method, $user_id);
  $stmt->execute();

  // إذا كانت "عمولات شحن" نسجلها أيضًا في جدول المعاملات
  if ($description === 'عمولات شحن') {
    $conn->query("INSERT INTO transactions (
      client_id, type, description, amount, exchange_rate, payment_method, container_id, created_at
    ) VALUES (
      NULL, 'قبض', 'عمولات شحن', $amount, 1, '$method', NULL, NOW()
    )");
  }
}

header("Location: cashbox.php");
exit;
?>