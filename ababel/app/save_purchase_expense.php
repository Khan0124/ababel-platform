<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $loading_number     = $_POST['loading_number'] ?? '';
  $client_code        = $_POST['client_code'] ?? '';
  $client_name        = $_POST['client_name'] ?? '';
  $container_number   = $_POST['container_number'] ?? '';

  $customs_amount         = $_POST['customs_amount'] ?? 0;
  $customs_additional     = $_POST['customs_additional'] ?? 0;
  $manifesto_amount       = $_POST['manifesto_amount'] ?? 0;
  $manifesto_additional   = $_POST['manifesto_additional'] ?? 0;
  $customs_profit         = $_POST['customs_profit'] ?? 0;
  $ports_amount           = $_POST['ports_amount'] ?? 0;
  $ports_additional       = $_POST['ports_additional'] ?? 0;
  $permission_amount      = $_POST['permission_amount'] ?? 0;
  $permission_additional = $_POST['permission_additional'] ?? 0;
  $yard_amount            = $_POST['yard_amount'] ?? 0;
  $yard_additional        = $_POST['yard_additional'] ?? 0;
  
   // 🔍 التحقق من التكرار
  $check = $conn->prepare("SELECT id FROM purchase_expenses WHERE loading_number = ?");
  $check->bind_param("s", $loading_number);
  $check->execute();
  $res = $check->get_result();
  if ($res->num_rows > 0) {
    echo "<script>alert('⚠️ هذا اللودنق تم تسجيله من قبل!'); window.location.href='purchase_expense.php';</script>";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO purchase_expenses (
    loading_number, client_code, client_name, container_number,
    customs_amount, customs_additional, manifesto_amount, manifesto_additional,
    customs_profit, ports_amount, ports_additional,
    permission_amount, permission_additional, yard_amount, yard_additional
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->bind_param(
    "ssssddddddddddd",
    $loading_number, $client_code, $client_name, $container_number,
    $customs_amount, $customs_additional,
    $manifesto_amount, $manifesto_additional,
    $customs_profit,
    $ports_amount, $ports_additional,
    $permission_amount, $permission_additional, $yard_amount, $yard_additional
  );

  if ($stmt->execute()) {
    header("Location: purchase_expense.php?success=1");
    exit;
  } else {
    die("❌ فشل في حفظ البيانات");
  }
} else {
  die("⚠️ طلب غير صالح");
}
