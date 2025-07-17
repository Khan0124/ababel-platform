<?php include 'config.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🧾 إضافة فاتورة مبيعات</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

  <style>
    body {
      background: #f8f9fa;
      font-family: 'Cairo', sans-serif;
      padding: 20px;
    }

    .form-section {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      max-width: 900px;
      margin: auto;
    }

    h4 {
      color: #711739;
      margin-bottom: 30px;
      font-weight: bold;
    }

    label {
      font-weight: 600;
      color: #444;
    }

    .btn-primary {
      background-color: #711739;
      border-color: #711739;
    }

    .btn-primary:hover {
      background-color: #5e1230;
      border-color: #5e1230;
    }

    .form-control:focus {
      border-color: #711739;
      box-shadow: 0 0 0 0.2rem rgba(113, 23, 57, 0.25);
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <h4>🧾 إضافة فاتورة مبيعات</h4>

    <form action="save_sales_invoice.php" method="POST" class="row g-4">

      <div class="col-md-4">
        <label for="invoice_date" class="form-label">📅 التاريخ:</label>
        <input type="date" id="invoice_date" name="invoice_date" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="invoice_number" class="form-label">🔢 رقم الفاتورة:</label>
        <input type="text" id="invoice_number" name="invoice_number" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="buyer_name" class="form-label">👤 اسم المشتري:</label>
        <input type="text" id="buyer_name" name="buyer_name" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="item_name" class="form-label">📦 الصنف:</label>
        <input type="text" id="item_name" name="item_name" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="carton_count" class="form-label">📦 عدد الكراتين:</label>
        <input type="number" id="carton_count" name="carton_count" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="invoice_value" class="form-label">💰 قيمة الفاتورة (جنيه):</label>
        <input type="number" id="invoice_value" step="0.01" name="invoice_value" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="vat_value" class="form-label">💸 القيمة المضافة (جنيه):</label>
        <input type="number" id="vat_value" step="0.01" name="vat_value" class="form-control" required>
      </div>

      <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-primary px-5 py-2">💾 حفظ الفاتورة</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
