<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? null;

if ($id) {
  $exp = $conn->query("SELECT * FROM daily_expenses WHERE id = $id")->fetch_assoc();
  if (!$exp) {
    die("❌ اليومية غير موجودة.");
  }

  // حذف اليومية
  $conn->query("DELETE FROM daily_expenses WHERE id = $id");

  // حذف العملية المرتبطة من cashbox
  $conn->query("DELETE FROM cashbox WHERE daily_expense_id = $id");

  header("Location: daily_expense_list.php?deleted=1");
  exit;
} else {
  die("❌ رقم اليومية غير موجود.");
}
