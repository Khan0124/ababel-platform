<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id             = intval($_POST['id']);
  $invoice_date   = $_POST['invoice_date'];
  $invoice_number = $_POST['invoice_number'];
  $buyer_name     = $_POST['buyer_name'];
  $item_name      = $_POST['item_name'];
  $carton_count   = $_POST['carton_count'];
  $invoice_value  = $_POST['invoice_value'];
  $vat_value      = $_POST['vat_value'];

  $stmt = $conn->prepare("UPDATE sales_invoices SET 
    invoice_date=?, invoice_number=?, buyer_name=?, item_name=?, 
    carton_count=?, invoice_value=?, vat_value=? 
    WHERE id=?");

  $stmt->bind_param("ssssiddi", $invoice_date, $invoice_number, $buyer_name, $item_name, $carton_count, $invoice_value, $vat_value, $id);
  $stmt->execute();

  header("Location: sales_invoices_list.php?updated=1");
  exit;
}
