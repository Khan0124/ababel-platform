<?php
include 'config.php';
include 'auth.php';

$users = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$user_list = [];
while($u = $users->fetch_assoc()) $user_list[$u['id']] = $u['full_name'];

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$user = $_GET['user'] ?? '';
$action = $_GET['action'] ?? '';

$where = "1";
if ($from && $to) $where .= " AND DATE(created_at) BETWEEN '$from' AND '$to'";
if ($user) $where .= " AND user_id = " . intval($user);
if ($action) $where .= " AND action = '" . mysqli_real_escape_string($conn, $action) . "'";

$res = $conn->query("SELECT * FROM activity_log WHERE $where ORDER BY created_at DESC LIMIT 200");
?><!DOCTYPE html><html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>سجل التعديلات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; padding: 30px; background: #f4f4f4; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; font-size: 14px; }
    .filters { margin-bottom: 20px; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="mb-4">سجل التعديلات والعمليات</h4>  <form method="get" class="row filters g-2">
    <div class="col-md-2">
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-3">
      <select name="user" class="form-select">
        <option value="">كل الموظفين</option>
        <?php foreach ($user_list as $id => $name): ?>
          <option value="<?= $id ?>" <?= $user == $id ? 'selected' : '' ?>><?= $name ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="action" class="form-select">
        <option value="">كل العمليات</option>
        <option <?= $action === 'إضافة' ? 'selected' : '' ?>>إضافة</option>
        <option <?= $action === 'تعديل' ? 'selected' : '' ?>>تعديل</option>
        <option <?= $action === 'حذف' ? 'selected' : '' ?>>حذف</option>
        <option <?= $action === 'قبض' ? 'selected' : '' ?>>قبض</option>
        <option <?= $action === 'صرف' ? 'selected' : '' ?>>صرف</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-dark w-100">🔍 تصفية</button>
    </div>
  </form>  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>التاريخ</th>
        <th>الموظف</th>
        <th>العملية</th>
        <th>النوع</th>
        <th>الرقم</th>
        <th>التفاصيل</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['created_at'] ?></td>
          <td><?= $user_list[$row['user_id']] ?? 'غير معروف' ?></td>
          <td><?= $row['action'] ?></td>
          <td><?= $row['reference_type'] ?></td>
          <td><?= $row['reference_id'] ?></td>
          <td><?= $row['details'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>