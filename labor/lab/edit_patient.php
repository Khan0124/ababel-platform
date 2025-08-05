<?php
include 'auth_check.php';
include '../includes/config.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['lab_id']);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) { die("المريض غير موجود."); }
$patient = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $age_value = $_POST['age_value'];
    $age_unit = $_POST['age_unit'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $history = $_POST['history'];

    $update = $conn->prepare("UPDATE patients SET name=?, gender=?, age_value=?, age_unit=?, phone=?, address=?, history=? WHERE id=?");
    $update->bind_param("ssissssi", $name, $gender, $age_value, $age_unit, $phone, $address, $history, $id);
    $update->execute();
    header("Location: view_patient.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل المريض</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">✏️ تعديل بيانات المريض</h4>
  <form method="post" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3"><input name="name" class="form-control" value="<?= $patient['name'] ?>" required></div>

    <div class="mb-3">
      <select name="gender" class="form-select" required>
        <option value="">-- الجنس --</option>
        <option value="ذكر" <?= $patient['gender'] == 'ذكر' ? 'selected' : '' ?>>ذكر</option>
        <option value="أنثى" <?= $patient['gender'] == 'أنثى' ? 'selected' : '' ?>>أنثى</option>
      </select>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">العمر</label>
        <input type="number" name="age_value" class="form-control" value="<?= $patient['age_value'] ?>" required>
      </div>
      <div class="col">
        <label class="form-label">الوحدة</label>
        <select name="age_unit" class="form-select" required>
          <option value="">-- اختر --</option>
          <option value="يوم" <?= $patient['age_unit'] == 'يوم' ? 'selected' : '' ?>>يوم</option>
          <option value="أسبوع" <?= $patient['age_unit'] == 'أسبوع' ? 'selected' : '' ?>>أسبوع</option>
          <option value="شهر" <?= $patient['age_unit'] == 'شهر' ? 'selected' : '' ?>>شهر</option>
          <option value="سنة" <?= $patient['age_unit'] == 'سنة' ? 'selected' : '' ?>>سنة</option>
        </select>
      </div>
    </div>

    <div class="mb-3"><input name="phone" class="form-control" value="<?= $patient['phone'] ?>"></div>
    <div class="mb-3"><input name="address" class="form-control" value="<?= $patient['address'] ?>"></div>
    <div class="mb-3"><textarea name="history" class="form-control"><?= $patient['history'] ?></textarea></div>

    <button class="btn btn-success">💾 حفظ</button>
    <a href="view_patient.php?id=<?= $id ?>" class="btn btn-secondary">رجوع</a>
  </form>
</div>
</body>
</html>
