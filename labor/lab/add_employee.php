<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$shifts = $conn->query("SELECT id, name FROM shifts WHERE lab_id = $lab_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $shift_id = $_POST['shift_id'];

    $stmt = $conn->prepare("INSERT INTO lab_employees (lab_id, name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $lab_id, $name, $email, $pass, $role, $status);
    $stmt->execute();
    $employee_id = $conn->insert_id;

    $conn->query("INSERT INTO employee_shifts (employee_id, shift_id) VALUES ($employee_id, $shift_id)");

    header("Location: employees_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة موظف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">➕ إضافة موظف جديد</h4>
  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3"><label class="form-label">الاسم</label><input type="text" name="name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">كلمة المرور</label><input type="password" name="password" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">الدور</label>
      <select name="role" class="form-select" required>
        <option value="محضر">محضر</option>
        <option value="طبيب مختبر">طبيب مختبر</option>
        <option value="محاسب">محاسب</option>
        <option value="مدير">مدير</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">الحالة</label>
      <select name="status" class="form-select">
        <option value="نشط">نشط</option>
        <option value="غير نشط">غير نشط</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">الوردية</label>
      <select name="shift_id" class="form-select" required>
        <?php while($s = $shifts->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-success">💾 حفظ</button>
    <a href="employees_list.php" class="btn btn-secondary">↩️ رجوع</a>
  </form>
</div>
</body>
</html>
