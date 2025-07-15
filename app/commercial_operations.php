<?php include 'config.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📚 العمليات التجارية</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background-color: #f4f4f4;
      padding: 30px;
    }
    .section-title {
      color: #711739;
      margin-bottom: 15px;
    }
    a {
      color: #333;
      font-weight: bold;
      text-decoration: none;
    }
    a:hover {
      color: #0056b3;
    }
    .card {
      border: none;
      border-right: 5px solid #711739;
      background-color: #ffffff;
      transition: transform 0.2s;
    }
    .card:hover {
      transform: scale(1.02);
    }
    .card-title {
      color: #711739;
    }
  </style>
</head>
<body>

<div class="container">

  <h3 class="mb-5 text-center">📚 العمليات التجارية</h3>

  <!-- مطالبات السجل -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title">📑 مطالبات السجل</h5>
      <ul class="list-unstyled">
        <li><a href="register_requests.php">➕ إضافة طلب سجل</a></li>
        <li><a href="register_requests_list.php">📋 قائمة طلبات السجل</a></li>
      </ul>
    </div>
  </div>

  <!-- مصروفات على المشتريات -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title">🧾 مصروفات على المشتريات</h5>
      <ul class="list-unstyled">
        <li><a href="purchase_expense.php">➕ إضافة مصروف على المشتريات</a></li>
        <li><a href="purchase_expense_report.php">📊 تقرير المصروفات</a></li>
      </ul>
    </div>
  </div>

  <!-- إدارة المبيعات -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title">📦 إدارة المبيعات</h5>
      <ul class="list-unstyled">
        <li><a href="add_sales_invoice.php">➕ إضافة فاتورة</a></li>
        <li><a href="sales_invoices_list.php">📄 قائمة الفواتير</a></li>
      </ul>
    </div>
  </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
