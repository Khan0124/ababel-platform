<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$shifts = $conn->query("SELECT * FROM shifts WHERE lab_id = $lab_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الورديات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">🕐 قائمة الورديات</h4>
  <a href="add_shift.php" class="btn btn-success mb-3">➕ إضافة وردية</a>
  <table class="table table-bordered table-striped text-center bg-white">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>الاسم</th>
        <th>البداية</th>
        <th>النهاية</th>
        <th>الأيام</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while($row = $shifts->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= $row['start_time'] ?></td>
        <td><?= $row['end_time'] ?></td>
        <td><?= htmlspecialchars($row['days']) ?></td>
        <td>
          <a href="edit_shift.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
          <a href="delete_shift.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
