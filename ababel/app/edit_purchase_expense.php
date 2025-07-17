<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);
$result = $conn->query("SELECT * FROM purchase_expenses WHERE id = $id");

if ($result->num_rows === 0) {
    die("🚫 السجل غير موجود.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل مصروف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style> body { font-family: 'Cairo', sans-serif; padding: 30px; } </style>
</head>
<body>
<div class="container">
  <h4 class="mb-4">✏️ تعديل المصروف رقم <?= $id ?></h4>
  <form method="POST" action="update_purchase_expense.php" class="row g-3">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="col-md-4">
      <label>قيمة مضافة جمركية:</label>
      <input type="number" step="0.01" name="customs_additional" value="<?= $row['customs_additional'] ?>" class="form-control">
    </div>

    <div class="col-md-4">
      <label>قيمة مضافة منفستو:</label>
      <input type="number" step="0.01" name="manifesto_additional" value="<?= $row['manifesto_additional'] ?>" class="form-control">
    </div>

    <div class="col-md-4">
      <label>أرباح أعمال جمركية:</label>
      <input type="number" step="0.01" name="customs_profit" value="<?= $row['customs_profit'] ?>" class="form-control">
    </div>

    <div class="col-md-4">
      <label>قيمة مضافة موانئ:</label>
      <input type="number" step="0.01" name="ports_additional" value="<?= $row['ports_additional'] ?>" class="form-control">
    </div>

    <div class="col-md-4">
      <label>قيمة مضافة أرضيات:</label>
      <input type="number" step="0.01" name="yard_additional" value="<?= $row['yard_additional'] ?>" class="form-control">
    </div>
<div class="col-md-4">
  <label>قيمة مضافة إذن:</label>
  <input type="number" step="0.01" name="permission_additional" value="<?= $row['permission_additional'] ?>" class="form-control">
</div>

    <div class="col-12 text-center mt-4">
      <button type="submit" class="btn btn-primary px-5">💾 حفظ التعديلات</button>
    </div>
  </form>
</div>
</body>
</html>
