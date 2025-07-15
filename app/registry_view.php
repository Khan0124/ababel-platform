<?php
include 'config.php';
include 'auth.php';

$registry_id = intval($_GET['id'] ?? 0);
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// اسم السجل
$registry = $conn->query("SELECT name FROM registers WHERE id = $registry_id")->fetch_assoc();
if (!$registry) die("❌ السجل غير موجود.");

// الاستعلام
$params = [$registry_id];
$types = "i";
$filter_sql = "";

if ($from && $to) {
  $filter_sql = " AND DATE(t.created_at) BETWEEN ? AND ?";
  $params[] = $from;
  $params[] = $to;
  $types .= "ss";
}

$sql = "
  SELECT t.*, c.container_number, cl.name AS client_name
  FROM transactions t
  LEFT JOIN containers c ON c.id = t.container_id
  LEFT JOIN clients cl ON cl.id = t.client_id
  WHERE t.register_id = ? AND t.description = 'سجل' $filter_sql
  ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// تجميع حسب العميل
$by_client = [];
$debit = $credit = 0;
$chart_data = ["مطالبة" => 0, "قبض" => 0];
$rows = [];

while($row = $result->fetch_assoc()) {
  $rows[] = $row;
  $chart_data[$row['type']] += $row['amount'];
  $client = $row['client_name'];
  $by_client[$client][$row['type']] = ($by_client[$client][$row['type']] ?? 0) + $row['amount'];
  if ($row['type'] === 'مطالبة') $debit += $row['amount'];
  if ($row['type'] === 'قبض') $credit += $row['amount'];
}
$balance = $debit - $credit;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تفاصيل السجل - <?= htmlspecialchars($registry['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-4">

  <h4 class="text-center mb-4">📑 تفاصيل السجل: <?= htmlspecialchars($registry['name']) ?></h4>

  <!-- فلترة بالتاريخ -->
  <form method="GET" class="row g-2 mb-4">
    <input type="hidden" name="id" value="<?= $registry_id ?>">
    <div class="col-md-4">
      <label>من تاريخ:</label>
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-4">
      <label>إلى تاريخ:</label>
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-dark w-100">🔍 تصفية</button>
    </div>
  </form>

  <!-- تصدير -->
  <div class="text-end mb-3">
    <a href="export_excel.php?register_id=<?= $registry_id ?>&from=<?= $from ?>&to=<?= $to ?>" class="btn btn-success btn-sm">📥 تصدير Excel</a>
    <a href="export_pdf.php?register_id=<?= $registry_id ?>&from=<?= $from ?>&to=<?= $to ?>" class="btn btn-danger btn-sm">🧾 تصدير PDF</a>
  </div>

  <!-- جدول المعاملات -->
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>التاريخ</th>
          <th>العميل</th>
          <th>نوع</th>
          <th>المبلغ</th>
          <th>الحاوية</th>
          <th>الإيصال</th>
          <th>الخيارات</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= $row['created_at'] ?></td>
          <td><?= htmlspecialchars($row['client_name']) ?></td>
          <td><?= $row['type'] ?></td>
          <td><?= number_format($row['amount'], 2) ?></td>
          <td><?= $row['container_number'] ?? '-' ?></td>
          <td><?= $row['serial'] ?? '-' ?></td>
          <td>
            <a href="receipt_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">👁️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <th colspan="3">الإجمالي</th>
          <th><?= number_format($balance, 2) ?> (الرصيد)</th>
          <th colspan="3">مطالبات: <?= number_format($debit, 2) ?> | مقبوضات: <?= number_format($credit, 2) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- رسم بياني -->
  <div class="card mt-5 p-3 shadow-sm">
    <h5 class="mb-3">📊 رسم بياني</h5>
    <canvas id="chart" height="120"></canvas>
  </div>

  <!-- تجميع حسب العميل -->
  <div class="card mt-4 p-3 shadow-sm">
    <h5 class="mb-3">👥 إجمالي لكل عميل</h5>
    <table class="table table-bordered text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>العميل</th>
          <th>مطالبات</th>
          <th>مقبوضات</th>
          <th>الرصيد</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($by_client as $name => $data): 
          $d = $data['مطالبة'] ?? 0;
          $c = $data['قبض'] ?? 0;
        ?>
          <tr>
            <td><?= htmlspecialchars($name) ?></td>
            <td><?= number_format($d, 2) ?></td>
            <td><?= number_format($c, 2) ?></td>
            <td><?= number_format($d - $c, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-4">
    <a href="registries.php" class="btn btn-secondary px-4">🔙 رجوع للسجلات</a>
  </div>
</div>

<!-- ChartJS رسم -->
<script>
  const ctx = document.getElementById('chart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['مطالبات', 'مقبوضات'],
      datasets: [{
        label: 'مبالغ السجل',
        data: [<?= $chart_data['مطالبة'] ?>, <?= $chart_data['قبض'] ?>],
        borderWidth: 1
      }]
    },
    options: {
      scales: { y: { beginAtZero: true } }
    }
  });
</script>
</body>
</html>
