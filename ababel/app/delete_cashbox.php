<?php
include 'config.php';
include 'auth.php';

// حذف جماعي
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
  $ids = array_map('intval', $_POST['ids']);
  $id_list = implode(',', $ids);
  $conn->query("DELETE FROM cashbox WHERE id IN ($id_list)");
  header("Location: cashbox.php?deleted=1");
  exit;
}

// حذف فردي
if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $conn->query("DELETE FROM cashbox WHERE id = $id");
  header("Location: cashbox.php?deleted=1");
  exit;
}

// لا توجد بيانات حذف
die("⚠️ لم يتم تحديد أي عملية للحذف.");
