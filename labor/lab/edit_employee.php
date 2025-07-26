<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$id = $_GET['id'] ?? 0;

// جلب الموظف
$stmt = $conn->prepare("SELECT * FROM lab_employees WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $lab_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
if (!$emp) { die("⚠️ الموظف غير موجود."); }

// جلب ورديات المعمل
$shifts = $conn->query("SELECT id, name FROM shifts WHERE lab_id = $lab_id");

// جلب الوردية الحالية
$current_shift = $conn->query("SELECT shift_id FROM employee_shifts WHERE employee_id = $id LIMIT 1")->fetch_assoc()['shift_id'] ?? null;

// عند التعديل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    $shift_id = $_POST['shift_id'];

    if ($password) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE lab_employees SET name=?, email=?, role=?, status=?, password=? WHERE id=? AND lab_id=?");
        $stmt->bind_param("sssssii", $name, $email, $role, $status, $hashed, $id, $lab_id);
    } else {
        $stmt = $conn->prepare("UPDATE lab_employees SET name=?, email=?, role=?, status=? WHERE id=? AND lab_id=?");
        $stmt->bind_param("ssssii", $name, $email, $role, $status, $id, $lab_id);
    }
    $stmt->execute();

    $conn->query("DELETE FROM employee_shifts WHERE employee_id = $id");
    $conn->query("INSERT INTO employee_shifts (employee_id, shift_id) VALUES ($id, $shift_id)");

    header("Location: employees_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل موظف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="text-primary mb-4">✏️ تعديل بيانات الموظف</h4>
  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3"><label class="form-label">الاسم</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($emp['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($emp['email']) ?>" required></div>
    <div class="mb-3"><label class="form-label">كلمة المرور الجديدة (اختياري)</label><input type="password" name="password" class="form-control"></div>
    <div class="mb-3"><label class="form-label">الدور</label>
      <select name="role" class="form-select">
        <option value="محضر" <?= $emp['role'] == 'محضر' ? 'selected' : '' ?>>محضر</option>
        <option value="طبيب مختبر" <?= $emp['role'] == 'طبيب مختبر' ? 'selected' : '' ?>>طبيب مختبر</option>
        <option value="محاسب" <?= $emp['role'] == 'محاسب' ? 'selected' : '' ?>>محاسب</option>
        <option value="مدير" <?= $emp['role'] == 'مدير' ? 'selected' : '' ?>>مدير</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">الحالة</label>
      <select name="status" class="form-select">
        <option value="نشط" <?= $emp['status'] == 'نشط' ? 'selected' : '' ?>>نشط</option>
        <option value="غير نشط" <?= $emp['status'] == 'غير نشط' ? 'selected' : '' ?>>غير نشط</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">الوردية</label>
      <select name="shift_id" class="form-select" required>
        <?php while($s = $shifts->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>" <?= $current_shift == $s['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-success">💾 حفظ التعديلات</button>
    <a href="employees_list.php" class="btn btn-secondary">↩️ رجوع</a>
  </form>
</div>
</body>
</html>
