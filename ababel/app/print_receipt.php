<?php
include 'config.php';
session_start();

$id = $_GET['id'] ?? 0;
$receipt = $conn->query("SELECT t.*, c.container_number, cl.name AS client_name, cl.code AS client_code
FROM transactions t
LEFT JOIN containers c ON t.container_id = c.id
LEFT JOIN clients cl ON t.client_id = cl.id
WHERE t.id = $id")->fetch_assoc();

if (!$receipt) {
  die("⚠️ الإيصال غير موجود.");
}

$usd_amount = $receipt['exchange_rate'] > 0 ? round($receipt['amount'] / $receipt['exchange_rate'], 2) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إيصال رسمي</title>
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      margin: 0;
      padding: 40px;
      background: #fff;
      position: relative;
    }
    .receipt {
      width: 600px;
      margin: auto;
      border: 2px solid #000;
      padding: 30px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }
    .header img {
      height: 80px;
    }
    .company-name {
      text-align: center;
      flex: 1;
    }
    .company-name h2, .company-name h4 {
      margin: 0;
    }
    .tax-number {
      text-align: left;
      font-size: 14px;
    }
    .section-title {
      text-align: center;
      margin: 20px 0 10px;
      font-size: 20px;
      font-weight: bold;
      border-bottom: 1px dashed #000;
      padding-bottom: 5px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
      font-size: 16px;
    }
    .signatures {
      margin-top: 50px;
      display: flex;
      justify-content: space-between;
    }
    .signatures div {
      width: 45%;
      text-align: center;
    }
    .qr-code {
      position: absolute;
      bottom: 20px;
      left: 30px;
      text-align: left;
    }
  </style>
</head>
<body>

<div class="receipt">
  <div class="header">
    <img src="logo.png" alt="شعار الشركة">
    <div class="company-name">
      <h2>شركة أبابيل للتنمية والاستثمار المحدودة</h2>
      <h4>ABABEL FOR DEVELOPMENT & INVESTMENT CO. LTD</h4>
    </div>
    <div class="tax-number">
      الرقم الضريبي:<br><strong>300001127808</strong>
    </div>
  </div>

  <div class="section-title">إيصال مالي</div>

  <div style="text-align: right; margin: 10px 0; font-size: 16px;">
    <strong>اسم العميل:</strong> <?= $receipt['client_name'] ?? '---' ?> &nbsp;&nbsp;&nbsp;
    <strong>كود العميل:</strong> <?= $receipt['client_code'] ?? '---' ?>
  </div>

  <table>
    <tr><th>رقم الإيصال</th><td><?= $receipt['serial'] ?></td></tr>
    <tr><th>تاريخ المعاملة</th><td><?= $receipt['created_at'] ?></td></tr>
    <tr><th>نوع المعاملة</th><td><?= $receipt['type'] ?></td></tr>
    <tr><th>البيان</th><td><?= $receipt['description'] ?></td></tr>
    <tr><th>المبلغ بالجنيه</th><td><?= number_format($receipt['amount'], 2) ?> ج.س</td></tr>
    <tr><th>سعر الصرف</th><td><?= $receipt['exchange_rate'] ?></td></tr>
    <tr><th>المبلغ بالدولار</th><td><?= number_format($usd_amount, 2) ?> USD</td></tr>
    <tr><th>طريقة الدفع</th><td><?= $receipt['payment_method'] ?></td></tr>
    <tr><th>رقم الحاوية</th><td><?= $receipt['container_number'] ?? '-' ?></td></tr>
  </table>

  <div class="signatures">
  <div>
    ................................................<br>
    توقيع المحاسب
  </div>
  <div>
    ................................................<br>
    توقيع العميل
  </div>
</div>

<!-- QR تحت التوقيع داخل الإطار -->
<div style="margin-top: 30px; text-align: left;">
  <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode('https://ababel.net/app/print_receipt.php?id=' . $id) ?>" alt="QR Code">
</div>


<script>window.onload = function() { window.print(); }</script>
</body>
</html>
