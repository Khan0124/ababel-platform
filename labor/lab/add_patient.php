<?php
include 'auth_employee.php';
include '../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $age_value = $_POST['age_value'];
    $age_unit = $_POST['age_unit'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $history = $_POST['history'];
    $lab_id = $_SESSION['lab_id'];

    // توليد كود فريد للمريض
    $code = 'P-' . rand(10000, 99999);
    $check = $conn->prepare("SELECT id FROM patients WHERE code = ?");
    $check->bind_param("s", $code);
    $check->execute();
    $check->store_result();
    while ($check->num_rows > 0) {
        $code = 'P-' . rand(10000, 99999);
        $check->bind_param("s", $code);
        $check->execute();
        $check->store_result();
    }

    $stmt = $conn->prepare("INSERT INTO patients (lab_id, code, name, gender, age_value, age_unit, phone, address, history)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssissss", $lab_id, $code, $name, $gender, $age_value, $age_unit, $phone, $address, $history);

    if ($stmt->execute()) {
        $success = "✅ تم إضافة المريض بنجاح.";
    } else {
        $error = "حدث خطأ أثناء حفظ البيانات.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة مريض</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4">➕ إضافة مريض جديد</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">الاسم الكامل</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">الجنس</label>
      <select name="gender" class="form-select" required>
        <option value="">-- اختر --</option>
        <option value="ذكر">ذكر</option>
        <option value="أنثى">أنثى</option>
      </select>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">العمر</label>
        <input type="number" name="age_value" class="form-control" min="0" required>
      </div>
      <div class="col">
        <label class="form-label">الوحدة</label>
        <select name="age_unit" class="form-select" required>
          <option value="">-- اختر --</option>
          <option value="يوم">يوم</option>
          <option value="أسبوع">أسبوع</option>
          <option value="شهر">شهر</option>
          <option value="سنة">سنة</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">رقم الهاتف (اختياري)</label>
      <input type="text" name="phone" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">العنوان</label>
      <input type="text" name="address" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">التاريخ المرضي</label>
      <textarea name="history" class="form-control" rows="3"></textarea>
    </div>

    <button class="btn btn-primary">💾 حفظ</button>
    <a href="patients_list.php" class="btn btn-secondary">رجوع</a>
  </form>
</div>
</body>
</html>
