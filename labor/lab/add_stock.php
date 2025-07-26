<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $quantity = floatval($_POST['quantity']);
    $unit = $_POST['unit'];
    $min_quantity = floatval($_POST['min_quantity']);
    $expiry_date = $_POST['expiry_date'] ?: null;

    $stmt = $conn->prepare("INSERT INTO stock_items (lab_id, name, type, quantity, unit, min_quantity, expiry_date)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsds", $lab_id, $name, $type, $quantity, $unit, $min_quantity, $expiry_date);
    if ($stmt->execute()) {
        $message = "✅ تم إضافة المادة بنجاح!";
    } else {
        $message = "❌ حدث خطأ أثناء الإضافة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>إضافة مادة للمخزن</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h4 class="mb-4 text-primary">➕ إضافة مادة جديدة</h4>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">اسم المادة</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">نوع المادة</label>
        <select name="type" class="form-select" required>
          <option value="مستهلك">مستهلك</option>
          <option value="دائم">دائم</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">الكمية</label>
        <input type="number" step="0.01" name="quantity" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">الوحدة</label>
        <input type="text" name="unit" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">حد التنبيه (الكمية الأدنى)</label>
        <input type="number" step="0.01" name="min_quantity" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">تاريخ الانتهاء (اختياري)</label>
      <input type="date" name="expiry_date" class="form-control">
    </div>

    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-success">💾 حفظ المادة</button>
      <a href="stock_list.php" class="btn btn-secondary">↩️ رجوع للقائمة</a>
    </div>
  </form>
</div>

</body>
</html>
