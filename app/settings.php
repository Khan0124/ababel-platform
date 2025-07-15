<?php
include 'config.php';
session_start();

// معالجة التحديث
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_rate = floatval($_POST['exchange_rate']);
    if ($new_rate > 0) {
        $conn->query("INSERT INTO settings (exchange_rate) VALUES ($new_rate)");
        $message = "✅ تم تحديث سعر الصرف بنجاح.";
    } else {
        $message = "⚠️ يجب إدخال قيمة صحيحة.";
    }
}

// جلب آخر سعر صرف
$rate = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$current_rate = $rate['exchange_rate'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إعدادات النظام</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Cairo', sans-serif; }
    .card { border-radius: 10px; }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="card p-4 shadow-sm">
    <h3 class="mb-4 text-center">⚙️ إعدادات النظام</h3>

    <?php if (isset($message)): ?>
      <div class="alert alert-info text-center"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 align-items-end">
      <div class="col-md-6">
        <label for="exchange_rate" class="form-label">سعر الصرف الحالي:</label>
        <input type="number" step="0.01" name="exchange_rate" id="exchange_rate" class="form-control" value="<?= $current_rate ?>" required>
      </div>

      <div class="col-md-6 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">💾 حفظ التغييرات</button>
        <a href="backup.php" class="btn btn-outline-dark w-100">📥 نسخة احتياطية</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
