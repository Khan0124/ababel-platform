<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $invoice_date     = $_POST['invoice_date'];
  $invoice_number   = $_POST['invoice_number'];
  $buyer_name       = $_POST['buyer_name'];
  $item_name        = $_POST['item_name'];
  $carton_count     = $_POST['carton_count'];
  $invoice_value    = $_POST['invoice_value'];
  $vat_value        = $_POST['vat_value'];

  // حفظ البيانات
  $stmt = $conn->prepare("INSERT INTO sales_invoices (invoice_date, invoice_number, buyer_name, item_name, carton_count, invoice_value, vat_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssidd", $invoice_date, $invoice_number, $buyer_name, $item_name, $carton_count, $invoice_value, $vat_value);
  $stmt->execute();

  header("Location: sales_invoices_list.php?success=1");
  exit;
}
?>
