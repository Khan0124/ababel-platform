<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$id = $_GET['id'] ?? 0;
$message = "";

// جلب البيانات الحالية
$stmt = $conn->prepare("SELECT * FROM stock_items WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $lab_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("⚠️ المادة غير موجودة.");
}

// عند الحفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $quantity = floatval($_POST['quantity']);
    $unit = $_POST['unit'];
    $min_quantity = floatval($_POST['min_quantity']);
    $expiry_date = $_POST['expiry_date'] ?: null;

    $stmt = $conn->prepare("UPDATE stock_items SET name=?, type=?, quantity=?, unit=?, min_quantity=?, expiry_date=? WHERE id=? AND lab_id=?");
    $stmt->bind_param("ssdsdsii", $name, $type, $quantity, $unit, $min_quantity, $expiry_date, $id, $lab_id);
    if ($stmt->execute()) {
        $message = "✅ تم التعديل بنجاح!";
    } else {
        $message = "❌ فشل في التحديث.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تعديل مادة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h4 class="mb-4 text-primary">✏️ تعديل مادة</h4>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">اسم المادة</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">نوع المادة</label>
        <select name="type" class="form-select">
          <option value="مستهلك" <?= $item['type'] == 'مستهلك' ? 'selected' : '' ?>>مستهلك</option>
          <option value="دائم" <?= $item['type'] == 'دائم' ? 'selected' : '' ?>>دائم</option>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">الكمية</label>
        <input type="number" step="0.01" name="quantity" class="form-control" value="<?= $item['quantity'] ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">الوحدة</label>
        <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($item['unit']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">حد التنبيه</label>
        <input type="number" step="0.01" name="min_quantity" class="form-control" value="<?= $item['min_quantity'] ?>" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">تاريخ الانتهاء</label>
      <input type="date" name="expiry_date" class="form-control" value="<?= $item['expiry_date'] ?>">
    </div>

    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
      <a href="stock_list.php" class="btn btn-secondary">↩️ رجوع للقائمة</a>
    </div>
  </form>
</div>

</body>
</html>
