<?php
include 'auth.php';
include 'config.php';

$id = $_GET['id'] ?? 0;

// بيانات اليومية
$receipt = $conn->query("SELECT cb.*, u.username FROM cashbox cb
  LEFT JOIN users u ON cb.user_id = u.id
  WHERE cb.id = $id AND cb.source='يومية قبض'")->fetch_assoc();

if (!$receipt) die("لم يتم العثور على اليومية");

// سعر الصرف
$exchange = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$rate = $exchange ? floatval($exchange->fetch_assoc()['exchange_rate']) : 1;

// حساب المبلغ بالدولار
$amount_sd = floatval($receipt['amount']);
$amount_usd = $rate > 0 ? round($amount_sd / $rate, 2) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طباعة يومية قبض</title>
  <style>
    body { font-family: 'Cairo', sans-serif; padding: 30px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid black; padding-bottom: 10px; }
    .logo { height: 60px; }
    .company { text-align: center; flex: 1; }
    .company h2, .company h4, .company p { margin: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: center; }
    .gray { background-color: #eee; }
    .signature { margin-top: 80px; }
    .signature img { height: 60px; }
  </style>
</head>
<body>

<div class="header">
  <div></div>
  <div class="company">
    <h2>شركة أبابيل للتنمية والاستثمار المحدودة</h2>
    <h4>ABABEL FOR DEVELOPMENT AND INVESTMENT CO .LTD</h4>
  </div>
  <img src="logo.png" class="logo" alt="logo">
</div>

<h3 style="text-align: center;">يومية قبض عمولات شحن رقم (<?= $receipt['id'] ?>)</h3>

<table style="margin-bottom: 10px;">
  <tr>
    <td><strong>اسم العميل:</strong> مكتب بورتسودان</td>
    <td><strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($receipt['created_at'])) ?></td>
    <td><strong>رقم العميل:</strong> ..................</td>
  </tr>
</table>

<table>
  <thead class="gray">
    <tr>
      <th>م</th>
      <th>البيان</th>
      <th>المبلغ المنصرف (جنيه)</th>
      <th>المبلغ دولار (USD)</th>
      <th>الإشعار</th>
      <th>الإيصال</th>
      <th>اليومية</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td><?= htmlspecialchars($receipt['description']) ?></td>
      <td><?= number_format($amount_sd, 0) ?></td>
      <td>$ <?= number_format($amount_usd, 2) ?></td>
      <td></td>
      <td></td>
      <td><?= $receipt['id'] ?></td>
    </tr>
    <tr class="gray">
      <td colspan="2"><strong>الإجمالي</strong></td>
      <td><strong><?= number_format($amount_sd, 0) ?></strong></td>
      <td><strong>$ <?= number_format($amount_usd, 2) ?></strong></td>
      <td colspan="3"></td>
    </tr>
  </tbody>
</table>

<div class="signature">
  <p>.........................................................</p>
  <p>الختم والتوقيع</p>
</div>

<script>window.print();</script>
</body>
</html>
