<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$logs = $conn->query("SELECT a.*, ad.name AS admin_name, l.name AS lab_name 
                      FROM activity_logs a 
                      JOIN admins ad ON a.admin_id = ad.id 
                      LEFT JOIN labs l ON a.lab_id = l.id 
                      ORDER BY a.created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>سجل الأنشطة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="mb-4">📋 سجل الأنشطة</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-dark">
        <tr>
          <th>المشرف</th>
          <th>المعمل</th>
          <th>العملية</th>
          <th>الوصف</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $logs->fetch_assoc()): ?>
        <tr>
          <td><?= $row['admin_name'] ?></td>
          <td><?= $row['lab_name'] ?? '—' ?></td>
          <td><?= $row['action_type'] ?></td>
          <td><?= $row['description'] ?></td>
          <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
