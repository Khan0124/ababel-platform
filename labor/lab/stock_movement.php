<?php
session_start();
include '../includes/auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$employee_id = $_SESSION['user_id'];
$stock_id = $_GET['id'] ?? 0;
$message = "";

// جلب اسم المادة
$stmt = $conn->prepare("SELECT name FROM stock_items WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $stock_id, $lab_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("⚠️ المادة غير موجودة أو لا تتبع هذا المعمل.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movement_type = $_POST['movement_type'];
    $quantity = floatval($_POST['quantity']);
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO stock_movements (stock_id, lab_id, movement_type, quantity, reason, employee_id)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisdsi", $stock_id, $lab_id, $movement_type, $quantity, $reason, $employee_id);
    if (!$stmt->execute()) {
        $message = "❌ حدث خطأ: " . $stmt->error;
    } else {
        // تحديث الكمية
        if ($movement_type === 'إدخال') {
            $conn->query("UPDATE stock_items SET quantity = quantity + $quantity WHERE id = $stock_id");
        } else {
            $conn->query("UPDATE stock_items SET quantity = quantity - $quantity WHERE id = $stock_id");
        }
        $message = "✅ تم تسجيل الحركة بنجاح.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تسجيل حركة مخزون</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h4 class="text-primary mb-4">🔄 تسجيل حركة للمادة: <span class="text-dark"><?= htmlspecialchars($item['name']) ?></span></h4>

  <?php if ($message): ?>
    <div class="alert <?= strpos($message, '❌') === 0 ? 'alert-danger' : 'alert-success' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">نوع الحركة</label>
      <select name="movement_type" class="form-select" required>
        <option value="إدخال">إدخال</option>
        <option value="إخراج">إخراج</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">الكمية</label>
      <input type="number" name="quantity" step="0.01" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">السبب / الجهة المستفيدة</label>
      <textarea name="reason" class="form-control" rows="3" required></textarea>
    </div>

    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-success">💾 حفظ الحركة</button>
      <a href="stock_list.php" class="btn btn-secondary">↩️ رجوع للمخزن</a>
    </div>
  </form>
</div>

</body>
</html>
