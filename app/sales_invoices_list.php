<?php
include 'config.php';
include 'auth.php';

$invoices = $conn->query("SELECT * FROM sales_invoices ORDER BY invoice_date DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📄 قائمة فواتير المبيعات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { background: #f4f4f4; font-family: 'Cairo', sans-serif; padding: 20px; }
    table { background: white; border-radius: 10px; overflow: hidden; }
    th, td { vertical-align: middle; font-size: 15px; }
    .btn-sm { font-size: 13px; }
  </style>
</head>
<body>

<div class="container">
  <h4 class="mb-4 text-center">📋 قائمة فواتير المبيعات</h4>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success text-center">✅ تم حفظ الفاتورة بنجاح</div>
  <?php endif; ?>

  <table class="table table-bordered text-center table-striped">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>التاريخ</th>
        <th>رقم الفاتورة</th>
        <th>المشتري</th>
        <th>الصنف</th>
        <th>الكراتين</th>
        <th>القيمة</th>
        <th>القيمة المضافة</th>
        <th>الخيارات</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $invoices->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= $row['invoice_date'] ?></td>
        <td><?= htmlspecialchars($row['invoice_number']) ?></td>
        <td><?= htmlspecialchars($row['buyer_name']) ?></td>
        <td><?= htmlspecialchars($row['item_name']) ?></td>
        <td><?= $row['carton_count'] ?></td>
        <td><?= number_format($row['invoice_value'], 2) ?></td>
        <td><?= number_format($row['vat_value'], 2) ?></td>
        <td>
          <a href="edit_sales_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
          <a href="print_sales_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">طباعة</a>
          <a href="delete_sales_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="text-center mt-3">
    <a href="add_sales_invoice.php" class="btn btn-primary">➕ فاتورة جديدة</a>
  </div>
</div>

</body>
</html>
