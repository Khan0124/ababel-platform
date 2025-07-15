<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id']);
$invoice = $conn->query("SELECT * FROM sales_invoices WHERE id = $id")->fetch_assoc();
if (!$invoice) die("🚫 الفاتورة غير موجودة");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل فاتورة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style> body { font-family: 'Cairo', sans-serif; padding: 30px; } </style>
</head>
<body>
<div class="container">
  <h4 class="mb-4">✏️ تعديل فاتورة مبيعات رقم <?= htmlspecialchars($invoice['invoice_number']) ?></h4>
  <form method="POST" action="update_sales_invoice.php" class="row g-3">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="col-md-4">
      <label>التاريخ:</label>
      <input type="date" name="invoice_date" value="<?= $invoice['invoice_date'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>رقم الفاتورة:</label>
      <input type="text" name="invoice_number" value="<?= $invoice['invoice_number'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>اسم المشتري:</label>
      <input type="text" name="buyer_name" value="<?= $invoice['buyer_name'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>الصنف:</label>
      <input type="text" name="item_name" value="<?= $invoice['item_name'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>عدد الكراتين:</label>
      <input type="number" name="carton_count" value="<?= $invoice['carton_count'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>قيمة الفاتورة:</label>
      <input type="number" step="0.01" name="invoice_value" value="<?= $invoice['invoice_value'] ?>" class="form-control" required>
    </div>

    <div class="col-md-4">
      <label>القيمة المضافة:</label>
      <input type="number" step="0.01" name="vat_value" value="<?= $invoice['vat_value'] ?>" class="form-control" required>
    </div>

    <div class="col-12 text-center mt-4">
      <button type="submit" class="btn btn-success px-5">💾 حفظ التعديلات</button>
    </div>
  </form>
</div>
</body>
</html>
