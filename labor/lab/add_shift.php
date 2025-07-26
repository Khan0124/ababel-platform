<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $days = $_POST['days'];
    $lab_id = $_SESSION['lab_id'];

    $stmt = $conn->prepare("INSERT INTO shifts (lab_id, name, start_time, end_time, days) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $lab_id, $name, $start, $end, $days);
    $stmt->execute();
    header("Location: shift_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>إضافة وردية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">➕ إضافة وردية جديدة</h4>
  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">اسم الوردية</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="row mb-3">
      <div class="col">
        <label class="form-label">وقت البداية</label>
        <input type="time" name="start_time" class="form-control" required>
      </div>
      <div class="col">
        <label class="form-label">وقت النهاية</label>
        <input type="time" name="end_time" class="form-control" required>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">الأيام (مثال: الأحد-الخميس)</label>
      <input type="text" name="days" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">💾 حفظ</button>
    <a href="shift_list.php" class="btn btn-secondary">↩️ رجوع</a>
  </form>
</div>
</body>
</html>
