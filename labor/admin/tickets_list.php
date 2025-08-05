<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$tickets = $conn->query("SELECT t.*, l.name AS lab_name FROM tickets t JOIN labs l ON t.lab_id = l.id ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تذاكر الدعم</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-4">🎫 تذاكر الدعم الفني</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>المعمل</th>
          <th>الموضوع</th>
          <th>الرسالة</th>
          <th>الحالة</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $tickets->fetch_assoc()): ?>
        <tr>
          <td><?= $row['lab_name'] ?></td>
          <td><?= $row['subject'] ?></td>
          <td><?= $row['message'] ?></td>
          <td>
            <span class="badge bg-<?= $row['status'] === 'open' ? 'warning' : 'secondary' ?>">
              <?= $row['status'] === 'open' ? 'مفتوحة' : 'مغلقة' ?>
            </span>
          </td>
          <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
