<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$result = $conn->query("SELECT * FROM cashbox WHERE lab_id = $lab_id ORDER BY created_at DESC");

// حساب الرصيد الحالي
$in = $conn->query("SELECT SUM(amount) FROM cashbox WHERE lab_id = $lab_id AND type = 'قبض'")->fetch_row()[0] ?? 0;
$out = $conn->query("SELECT SUM(amount) FROM cashbox WHERE lab_id = $lab_id AND type = 'صرف'")->fetch_row()[0] ?? 0;
$balance = $in - $out;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>الخزنة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="text-primary mb-4">💰 إدارة الخزنة</h4>

  <div class="alert alert-info fs-5">الرصيد الحالي: <strong class="text-success"><?= number_format($balance, 2) ?> ج.س</strong></div>

  <a href="add_transaction.php?type=قبض" class="btn btn-success mb-3">➕ إضافة قبض</a>
  <a href="add_transaction.php?type=صرف" class="btn btn-danger mb-3 ms-2">➖ إضافة صرف</a>

  <div class="table-responsive">
    <table class="table table-bordered table-hover text-center bg-white">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>النوع</th>
          <th>البيان</th>
          <th>المبلغ</th>
          <th>الطريقة</th>
          <th>ملاحظات</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><span class="badge bg-<?= $row['type'] === 'قبض' ? 'success' : 'danger' ?>"><?= $row['type'] ?></span></td>
          <td><?= htmlspecialchars($row['source']) ?></td>
          <td><?= number_format($row['amount'], 2) ?></td>
          <td><?= htmlspecialchars($row['method']) ?></td>
          <td><?= htmlspecialchars($row['notes']) ?></td>
          <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
