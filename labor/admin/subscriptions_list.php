<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$subs = $conn->query("SELECT s.*, l.name AS lab_name FROM subscriptions s JOIN labs l ON s.lab_id = l.id ORDER BY s.end_date DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>الاشتراكات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="mb-4">📦 اشتراكات المعامل</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-dark">
        <tr>
          <th>المعمل</th>
          <th>الخطة</th>
          <th>من</th>
          <th>إلى</th>
          <th>الحالة</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $subs->fetch_assoc()): ?>
        <tr>
          <td><?= $row['lab_name'] ?></td>
          <td><?= $row['plan'] ?></td>
          <td><?= $row['start_date'] ?></td>
          <td><?= $row['end_date'] ?></td>
          <td>
            <span class="badge bg-<?= $row['status'] === 'active' ? 'success' : ($row['status'] === 'expired' ? 'danger' : 'warning') ?>">
              <?= $row['status'] ?>
            </span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
