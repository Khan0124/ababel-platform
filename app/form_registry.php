
<?php
include 'config.php';
include 'auth.php';

$rate_result = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$exchange_rate = $rate_result ? $rate_result->fetch_assoc()['exchange_rate'] : 0;

$clients = $conn->query("SELECT id, name, code FROM clients ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>نموذج صرف سجل</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 20px; }
    .card { padding: 20px; border-radius: 10px; background: white; box-shadow: 0 0 10px #ccc; }
    td, th { text-align: center; }
    .btn-sm { padding: 3px 8px; }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <h4 class="mb-4">🧾 نموذج صرف سجل</h4>
    <form method="POST" action="save_registry_expense.php">
      <div class="row mb-3">
        <div class="col-md-4">
          <label>اسم العميل:</label>
          <select name="client_id" class="form-select" required>
            <option value="">اختر</option>
            <?php while($c = $clients->fetch_assoc()): ?>
              <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label>المبلغ بالجنيه:</label>
          <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>طريقة الدفع:</label>
          <select name="method" class="form-select" required>
            <option>كاش</option>
            <option>بنكك</option>
            <option>أوكاش</option>
            <option>فوري</option>
            <option>شيك</option>
          </select>
        </div>
      </div>
      <input type="hidden" name="exchange_rate" value="<?= $exchange_rate ?>">
      <div class="text-end">
        <button class="btn btn-success">💾 حفظ العملية</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
