<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$employees = $conn->query("SELECT * FROM lab_employees WHERE lab_id = $lab_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>قائمة الموظفين</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">👥 قائمة الموظفين</h4>
  <a href="add_employee.php" class="btn btn-success mb-3">➕ إضافة موظف</a>
  <table class="table table-bordered table-hover text-center bg-white">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>الاسم</th>
        <th>الإيميل</th>
        <th>الدور</th>
        <th>الحالة</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; while($row = $employees->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><span class="badge bg-<?= $row['status'] === 'نشط' ? 'success' : 'secondary' ?>"><?= $row['status'] ?></span></td>
        <td>
          <a href="edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
          <a href="delete_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
