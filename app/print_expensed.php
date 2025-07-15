<?php
include 'config.php';


$id = intval($_GET['id'] ?? 0);
$exp = $conn->query("SELECT * FROM daily_expenses WHERE id = $id")->fetch_assoc();

if (!$exp) {
  echo "<div style='color: red; text-align: center; margin-top: 50px; font-size: 20px;'>⚠️ المصروف المطلوب غير موجود في قاعدة البيانات.</div>";
  exit;
}

$client = $conn->query("SELECT name, code FROM clients WHERE id = " . intval($exp['client_id']))->fetch_assoc();
$client_name = $client['name'] ?? 'غير معروف';
$client_code = $client['code'] ?? '-';
$container = $conn->query("SELECT container_number FROM containers WHERE id = " . intval($exp['container_id']))->fetch_assoc();
$container_number = $container['container_number'] ?? '-';

$items = json_decode($exp['items_json'], true);
$date = date('Y-m-d', strtotime($exp['created_at']));
$day_ref = date('Ymd', strtotime($exp['created_at']));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طباعة يومية</title>
  <style>
    body { font-family: 'Cairo', sans-serif; padding: 30px; font-size: 14px; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
    .logo { height: 60px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: center; }
    .footer { margin-top: 40px; text-align: center; font-size: 13px; }
  </style>
</head>
<body>
<div class="header">
  <img src="logo.png" alt="Logo" class="logo">
  <div style="text-align: center; flex:1">
    <h3 style="margin:0;">شركة أبابيل للتنمية والاستثمار المحدودة</h3>
    <h5 style="margin:0;">Ababel Development & Investment Co. Ltd.</h5>
    <div>الرقم الضريبي: 300001127808</div>
    <div>مكتب بورتسودان</div>
  </div>
</div>

<h4 style="text-align:center">يومية <?= $exp['type'] ?> رقم (<?= $id ?>)</h4>
<p style="text-align:center; font-weight: bold; margin: 5px 0 15px;">إيصال المكتب</p>

<table>
  <tr>
    <td><strong>رقم الحاوية:</strong> <?= $container_number ?></td>
    <td><strong>رقم العميل:</strong> <?= $client_code ?></td>
    <td><strong>اسم العميل:</strong> <?= $client_name ?></td>
    <td><strong>التاريخ:</strong> <?= $date ?></td>
  </tr>
</table>

<table>
  <thead>
    <tr>
      <th>م</th>
      <th>البيان</th>
      <th>المبلغ المنصرف</th>
      <th>المبلغ دولار</th>
      <th>رقم الإيصال</th>
      <th>اليومية</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $i = 1; $total = 0; $usd = 0;
    foreach ($items as $item):
      $total += $item['amount'];
      $usd += $item['usd'];
      $receipt = $item['ref'] ?? '';
    ?>
    <tr>
      <td><?= $i ?></td>
      <td><?= $item['desc'] ?></td>
      <td><?= number_format($item['amount'], 2) ?></td>
      <td>$ <?= number_format($item['usd'], 2) ?></td>
      <td><?= $receipt !== '-' ? $receipt : '' ?></td>
      <td><?= $i === 1 ? $id : '' ?></td>
    </tr>
    <?php $i++; endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <th colspan="2">الإجمالي</th>
      <th><?= number_format($total, 2) ?></th>
      <th>$ <?= number_format($usd, 2) ?></th>
      <th colspan="2"></th>
    </tr>
  </tfoot>
</table>

<div class="footer">
  <br><br><br>
  <table style="margin:auto; width: 60%;">
    <tr>
      <td><strong>اسم المنسق:</strong> .....................</td>
      <td><strong>مدير المكتب:</strong> .....................</td>
    </tr>
  </table>
</div>

<div style="position: absolute; bottom: 30px; left: 30px; text-align: left;">
  <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode('https://ababel.net/app/print_expensed.php?id=' . $id) ?>" alt="QR Code">

</div>


<script>window.print();</script>
</body>
</html>
