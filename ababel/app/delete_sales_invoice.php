<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
  $conn->query("DELETE FROM sales_invoices WHERE id = $id");
  header("Location: sales_invoices_list.php?deleted=1");
  exit;
} else {
  header("Location: sales_invoices_list.php?error=1");
  exit;
}
