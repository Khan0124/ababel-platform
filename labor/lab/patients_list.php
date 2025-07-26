<?php
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$result = $conn->query("SELECT * FROM patients WHERE lab_id = $lab_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>قائمة المرضى</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    .table td, .table th {
      vertical-align: middle;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary">👨‍⚕️ قائمة المرضى</h4>
    <a href="add_patient.php" class="btn btn-success">➕ إضافة مريض جديد</a>
  </div>

  <div class="table-responsive bg-white shadow-sm rounded p-3">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>الكود</th>
          <th>الاسم</th>
          <th>الجنس</th>
          <th>العمر</th>
          <th>الهاتف</th>
          <th>العنوان</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['code'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['gender'] ?></td>
            <td><?= $row['age_value'] . ' ' . $row['age_unit'] ?></td>
            <td><?= $row['phone'] ?: '-' ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td>
              <a href="view_patient.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">عرض</a>
              <a href="edit_patient.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
              <a href="delete_patient.php?id=<?= $row['id'] ?>" onclick="return confirm('هل أنت متأكد من الحذف؟')" class="btn btn-sm btn-danger">حذف</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
