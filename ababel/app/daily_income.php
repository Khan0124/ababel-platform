<?php
include 'auth.php';
include 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $description = trim($_POST['description']);
  $amount = floatval($_POST['amount']);
  $user_id = $_SESSION['user_id'];
  $date = date("Y-m-d H:i:s");

  if (!$description || $amount <= 0) {
    $message = "⚠️ البيان أو المبلغ غير صالح.";
  } else {
    $stmt = $conn->prepare("INSERT INTO cashbox (type, description, amount, user_id, created_at, source)
                            VALUES ('قبض', ?, ?, ?, ?, 'يومية قبض')");
    $stmt->bind_param("sdis", $description, $amount, $user_id, $date);
    $stmt->execute();
    $message = "✅ تم حفظ اليومية بنجاح.";
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة يومية قبض</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <style> body { font-family: 'Cairo', sans-serif; padding: 30px; } </style>
</head>
<body class="bg-light">
  <div class="container card shadow-sm p-4" style="max-width: 600px;">
    <h4 class="mb-3 text-center">➕ إضافة يومية قبض</h4>

    <?php if ($message): ?>
      <div class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-warning' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
  <label class="form-label">البيان:</label>
  <select name="description" class="form-select" required>
    <option value="">اختر البيان</option>
    <option value="عمولات شحن">عمولات شحن</option>
    <!-- يمكنك إضافة عناصر جديدة هنا -->
  </select>
</div>


      <div class="mb-3">
        <label class="form-label">المبلغ:</label>
        <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
      </div>

      <div class="mb-3">
        <label class="form-label">اسم العميل (اختياري - لا يتم حفظه):</label>
        <input type="text" class="form-control" placeholder="يُستخدم للعرض فقط">
      </div>

      <button type="submit" class="btn btn-primary w-100">💾 حفظ اليومية</button>
    </form>

    <hr>
    <a href="daily_income_list.php" class="btn btn-outline-secondary w-100 mt-2">📋 عرض يوميات القبض</a>
  </div>
</body>
</html>
