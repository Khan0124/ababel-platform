<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);
$exp = $conn->query("SELECT * FROM daily_expenses WHERE id = $id")->fetch_assoc();

if (!$exp) {
  echo "<div style='color: red; text-align: center; margin-top: 50px; font-size: 20px;'>⚠️ المصروف المطلوب غير موجود في قاعدة البيانات.</div>";
  echo "<div style='text-align:center; margin-top: 20px;'><a href='daily_expense_list.php' class='btn btn-dark'>🔙 الرجوع للقائمة</a></div>";
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
  <title>عرض يومية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 30px; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: center; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="text-center mb-4">عرض تفاصيل يومية صرف رقم (<?= $id ?>)</h4>

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
        <th>إيصال المكتب</th>
        <th>اليومية</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; $total = 0; $usd = 0;
      foreach ($items as $item): 
        $total += $item['amount'];
        $usd += $item['usd'];
        $receipt = $item['ref'] ?? '-';
        $office_code = $day_ref . '-' . $i;
      ?>
        <tr>
          <td><?= $i ?></td>
          <td><?= $item['desc'] ?></td>
          <td><?= number_format($item['amount'], 2) ?></td>
          <td>$ <?= number_format($item['usd'], 2) ?></td>
          <td><?= $receipt ?></td>
          <td><?= $office_code ?></td>
          <td><?= $id ?></td>
        </tr>
      <?php $i++; endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="2">الإجمالي</th>
        <th><?= number_format($total, 2) ?></th>
        <th>$ <?= number_format($usd, 2) ?></th>
        <th colspan="3"></th>
      </tr>
    </tfoot>
  </table>

  <a href="daily_expense_list.php" class="btn btn-secondary mt-4">🔙 رجوع</a>
</div>
</body>
</html>
