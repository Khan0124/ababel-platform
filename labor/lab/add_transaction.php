<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'auth_check.php';
include '../includes/config.php';

$type = $_GET['type'] ?? 'قبض';
$lab_id = $_SESSION['lab_id'];
$employee_id = $_SESSION['employee_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $_POST['source'];
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO cashbox (lab_id, type, source, amount, method, notes, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdssi", $lab_id, $type, $source, $amount, $method, $notes, $employee_id);
    $stmt->execute();
    header("Location: cashbox.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة <?= $type ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">➕ إضافة <?= $type ?></h4>
  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">البيان</label>
      <input type="text" name="source" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">المبلغ</label>
      <input type="number" name="amount" step="0.01" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">طريقة الدفع</label>
      <select name="method" class="form-select" required>
        <option value="كاش">كاش</option>
        <option value="تحويل بنكي">تحويل بنكي</option>
        <option value="بطاقة">بطاقة</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">ملاحظات</label>
      <textarea name="notes" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-success">💾 حفظ</button>
    <a href="cashbox.php" class="btn btn-secondary">↩️ رجوع</a>
  </form>
</div>
</body>
</html>
