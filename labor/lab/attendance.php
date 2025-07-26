<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';
$employee_id = $_SESSION['employee_id'];
$today = date('Y-m-d');

// جلب الوردية الحالية للموظف
$stmt = $conn->prepare("SELECT s.id, s.name, s.start_time, s.end_time FROM shifts s
  JOIN employee_shifts es ON es.shift_id = s.id
  WHERE es.employee_id = ? AND es.is_active = 1 LIMIT 1");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$shift = $stmt->get_result()->fetch_assoc();

if (!$shift) {
    die("⚠️ لا توجد وردية محددة لهذا الموظف.");
}

$shift_id = $shift['id'];

// التحقق إذا تم تسجيل حضور اليوم
$check = $conn->prepare("SELECT * FROM employee_attendance WHERE employee_id = ? AND date = ?");
$check->bind_param("is", $employee_id, $today);
$check->execute();
$attendance = $check->get_result()->fetch_assoc();

// عند الضغط على بدء
if (isset($_POST['check_in'])) {
    if (!$attendance) {
        $now = date('Y-m-d H:i:s');
        $insert = $conn->prepare("INSERT INTO employee_attendance (employee_id, shift_id, date, check_in) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiss", $employee_id, $shift_id, $today, $now);
        $insert->execute();
        header("Location: attendance.php");
        exit;
    }
}

// عند الضغط على إنهاء
if (isset($_POST['check_out'])) {
    if ($attendance && !$attendance['check_out']) {
        $now = date('Y-m-d H:i:s');
        $update = $conn->prepare("UPDATE employee_attendance SET check_out = ? WHERE id = ?");
        $update->bind_param("si", $now, $attendance['id']);
        $update->execute();
        header("Location: attendance.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تسجيل الحضور</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="text-primary mb-4">🕒 إدارة حضور الموظف</h4>

  <div class="card p-3 mb-4 shadow-sm">
    <h5 class="mb-2">الوردية الحالية: <span class="text-success"><?= htmlspecialchars($shift['name']) ?></span></h5>
    <p>من <?= $shift['start_time'] ?> إلى <?= $shift['end_time'] ?></p>
  </div>

  <?php if (!$attendance): ?>
    <form method="POST">
      <button name="check_in" class="btn btn-success">✅ بدء الوردية</button>
    </form>
  <?php elseif ($attendance && !$attendance['check_out']): ?>
    <div class="alert alert-info">تم تسجيل الدخول الساعة: <?= date('H:i', strtotime($attendance['check_in'])) ?></div>
    <form method="POST">
      <button name="check_out" class="btn btn-danger">🔴 إنهاء الوردية</button>
    </form>
  <?php else: ?>
    <div class="alert alert-success">✅ تم إنهاء الوردية بنجاح الساعة: <?= date('H:i', strtotime($attendance['check_out'])) ?></div>
  <?php endif; ?>

  <a href="lab_dashboard.php" class="btn btn-secondary mt-3">↩️ العودة للوحة التحكم</a>
</div>
</body>
</html>
