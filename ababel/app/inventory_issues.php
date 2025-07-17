<?php include 'config.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🧾 صفحة المخزون</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 30px; }
    .form-section { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    h4 { color: #711739; }
    label { font-weight: bold; }
  </style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <h4 class="mb-4">📦 تسجيل مشاكل المخزون</h4>
    <form action="save_inventory_issues.php" method="POST" class="row g-3">

      <h5>البضاعة المفقودة</h5>
      <div class="col-md-4">
        <label>نوع البضاعة:</label>
        <input type="text" name="lost_type" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>العدد:</label>
        <input type="number" name="lost_quantity" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>القيمة:</label>
        <input type="number" step="0.01" name="lost_value" class="form-control" required>
      </div>

      <hr class="my-4">

      <h5>البضاعة التالفة</h5>
      <div class="col-md-4">
        <label>نوع البضاعة:</label>
        <input type="text" name="damaged_type" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>العدد:</label>
        <input type="number" name="damaged_quantity" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>القيمة:</label>
        <input type="number" step="0.01" name="damaged_value" class="form-control" required>
      </div>

      <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-primary px-5">💾 حفظ البيانات</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
