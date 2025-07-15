
<?php
include 'config.php';
session_start();

$id = $_GET['id'] ?? 0;
$receipt = $conn->query("SELECT * FROM transactions WHERE id = $id")->fetch_assoc();
if (!$receipt) {
  die("⚠️ الإيصال غير موجود.");
}

$containers = $conn->query("SELECT id, container_number FROM containers");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل الإيصال</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <div class="card p-4 shadow-sm">
    <h3 class="mb-4 text-center">✏️ تعديل الإيصال</h3>
    <form action="update_receipt.php" method="POST">
      <input type="hidden" name="id" value="<?= $receipt['id'] ?>">

      <div class="mb-3">
        <label>نوع المعاملة:</label>
        <select name="type" class="form-control" required>
          <option <?= $receipt['type'] == 'مطالبة' ? 'selected' : '' ?>>مطالبة</option>
          <option <?= $receipt['type'] == 'قبض' ? 'selected' : '' ?>>قبض</option>
        </select>
      </div>

      <div class="mb-3">
        <label>البيان:</label>
        <select name="description" class="form-control" required>
          <?php
          $options = ['سجل', 'موانئ', 'أرضيات', 'تختيم'];
          foreach ($options as $opt):
          ?>
            <option <?= $receipt['description'] == $opt ? 'selected' : '' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label>المبلغ (جنيه):</label>
        <input type="number" name="amount" class="form-control" value="<?= $receipt['amount'] ?>" required min="1">
      </div>

      <div class="mb-3">
        <label>طريقة الدفع:</label>
        <select name="payment_method" class="form-control">
          <?php
          $methods = ['كاش', 'بنكك', 'أوكاش', 'فوري', 'شيك'];
          foreach ($methods as $method):
          ?>
            <option <?= $receipt['payment_method'] == $method ? 'selected' : '' ?>><?= $method ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label>الحاوية:</label>
        <select name="container_id" class="form-control" required>
          <?php while($c = $containers->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $receipt['container_id'] == $c['id'] ? 'selected' : '' ?>>
              <?= $c['container_number'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label>سعر الصرف (غير قابل للتعديل):</label>
        <input type="number" class="form-control" value="<?= $receipt['exchange_rate'] ?>" readonly>
      </div>

      <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
    </form>
  </div>
</div>
</body>
</html>
