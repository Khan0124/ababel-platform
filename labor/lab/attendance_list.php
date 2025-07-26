<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// جلب قائمة الموظفين للفلترة
$employees = $conn->query("SELECT id, name FROM lab_employees WHERE lab_id = $lab_id");

// استقبال الفلاتر
$filter_employee = $_GET['employee_id'] ?? '';
$filter_date = $_GET['date'] ?? '';

$where = "WHERE e.lab_id = $lab_id";
if ($filter_employee) {
    $where .= " AND e.id = " . intval($filter_employee);
}
if ($filter_date) {
    $where .= " AND a.date = '" . $conn->real_escape_string($filter_date) . "'";
}

$result = $conn->query("
  SELECT a.*, e.name AS employee_name, s.name AS shift_name 
  FROM employee_attendance a
  JOIN lab_employees e ON a.employee_id = e.id
  JOIN shifts s ON a.shift_id = s.id
  $where
  ORDER BY a.date DESC, a.check_in DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>سجل الحضور</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">📋 سجل حضور الموظفين</h4>

  <form method="GET" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
    <div class="col-md-4">
      <label class="form-label">الموظف</label>
      <select name="employee_id" class="form-select">
        <option value="">-- الكل --</option>
        <?php while($emp = $employees->fetch_assoc()): ?>
          <option value="<?= $emp['id'] ?>" <?= $filter_employee == $emp['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($emp['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">التاريخ</label>
      <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>" class="form-control">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-primary">🔍 بحث</button>
      <a href="attendance_list.php" class="btn btn-secondary">↺ إعادة تعيين</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center bg-white">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>الموظف</th>
          <th>الوردية</th>
          <th>التاريخ</th>
          <th>وقت الدخول</th>
          <th>وقت الخروج</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['employee_name']) ?></td>
          <td><?= htmlspecialchars($row['shift_name']) ?></td>
          <td><?= $row['date'] ?></td>
          <td><?= $row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-' ?></td>
          <td><?= $row['check_out'] ? date('H:i', strtotime($row['check_out'])) : '-' ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <a href="lab_dashboard.php" class="btn btn-secondary mt-3">↩️ العودة للوحة التحكم</a>
</div>
</body>
</html>
