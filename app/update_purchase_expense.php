<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);

  $customs_additional   = $_POST['customs_additional'] ?? 0;
  $manifesto_additional = $_POST['manifesto_additional'] ?? 0;
  $customs_profit       = $_POST['customs_profit'] ?? 0;
  $ports_additional     = $_POST['ports_additional'] ?? 0;
  $yard_additional      = $_POST['yard_additional'] ?? 0;
  $permission_additional = $_POST['permission_additional'] ?? 0;

  $stmt = $conn->prepare("UPDATE purchase_expenses SET 
    customs_additional = ?, 
    manifesto_additional = ?, 
    customs_profit = ?, 
    ports_additional = ?, 
    yard_additional = ? ,
    permission_additional = ?
    WHERE id = ?");
    
  $stmt->bind_param("ddddddi", 
    $customs_additional, 
    $manifesto_additional, 
    $customs_profit, 
    $ports_additional, 
    $yard_additional,
    $permission_additional,
    $id
  );

  if ($stmt->execute()) {
    header("Location: purchase_expense_report.php?updated=1");
    exit;
  } else {
    die("❌ فشل التحديث.");
  }
}
