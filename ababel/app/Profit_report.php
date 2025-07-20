<?php
include 'auth.php';
include 'config.php';

$start = $_GET['from'] ?? '';
$end = $_GET['to'] ?? '';
$filter = $_GET['filter'] ?? '';

$date_condition = '';
if ($start && $end) {
  $date_condition = "AND created_at BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}

$items = [
  'موانئ' => 'صرف موانئ',
  'سجل' => 'صرف سجل',
  'تختيم' => 'صرف تختيم',
  'عمولات شحن' => null
];

$profit_data = [];
$total_profit = 0;

foreach ($items as $label => $source) {
  if ($filter && $filter !== $label) continue;

  // الإيرادات
  if ($label === 'عمولات شحن') {
    $rev = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE description='عمولات شحن' AND type='قبض' $date_condition")->fetch_assoc()['total'] ?? 0;
    $cost = 0;
  } elseif ($label === 'سجل') {
    // فقط سجل أبابيل (register_id = 4)
    $rev = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE description='سجل' AND type='قبض' AND register_id = 4 $date_condition")->fetch_assoc()['total'] ?? 0;
    $cost = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE source='صرف سجل' AND description='أبابيل' AND type='صرف' $date_condition")->fetch_assoc()['total'] ?? 0;
  } else {
    $rev = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE description='$label' AND type='قبض' $date_condition")->fetch_assoc()['total'] ?? 0;
    $cost = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE source='$source' AND type='صرف' $date_condition")->fetch_assoc()['total'] ?? 0;
  }

  $profit = $rev - $cost;
  $profit_data[$label] = [
    'revenue' => $rev,
    'cost' => $cost,
    'profit' => $profit
  ];
  $total_profit += $profit;
}


// خصومات
$gov_total = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE source='إجراءات حكومية' AND type='صرف' $date_condition")->fetch_assoc()['total'] ?? 0;
$office_total = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE source='مصروفات مكتب' AND type='صرف' $date_condition")->fetch_assoc()['total'] ?? 0;

$net_profit = $total_profit - $gov_total - $office_total;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تقرير الأرباح</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

  
</head>
<body style="font-family:'Cairo',sans-serif;background:#f4f4f4;padding:30px">
  <div class="container bg-white p-4 rounded shadow">
    <h4 class="text-center mb-4">📊 تقرير الأرباح</h4>

    <form method="get" class="text-center mb-4">
      <label>من:
        <input type="date" name="from" value="<?= htmlspecialchars($start) ?>" class="form-control d-inline-block w-auto">
      </label>
      <label>إلى:
        <input type="date" name="to" value="<?= htmlspecialchars($end) ?>" class="form-control d-inline-block w-auto">
      </label>
      <label>البند:
        <select name="filter" class="form-select d-inline-block w-auto">
          <option value="">الكل</option>
          <?php foreach ($items as $i => $src): ?>
            <option value="<?= $i ?>" <?= $filter === $i ? 'selected' : '' ?>><?= $i ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn-dark">🔍 فلترة</button>
      <button type="button" class="btn btn-success" onclick="exportExcel()">📥 Excel</button>
      <button id="printBtn" class="btn btn-secondary">🖨️ طباعة التقرير</button>
    </form>

    <table id="profitTable" class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>البند</th>
          <th>القبض</th>
          <th>التكلفة</th>
          <th>الربح</th>
          <th>النسبة (%)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($profit_data as $name => $data): ?>
          <tr>
            <td><?= $name ?></td>
            <td><?= number_format($data['revenue'], 2) ?></td>
            <td><?= number_format($data['cost'], 2) ?></td>
            <td><?= number_format($data['profit'], 2) ?></td>
            <td><?= $data['revenue'] > 0 ? round($data['profit'] / $data['revenue'] * 100, 2) : 0 ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr><th colspan="4">إجمالي الربح</th><th><?= number_format($total_profit, 2) ?></th></tr>
        <tr><th colspan="4">خصم مصروفات المكتب</th><th class="text-danger">-<?= number_format($office_total, 2) ?></th></tr>
        <tr><th colspan="4">خصم الإجراءات الحكومية</th><th class="text-danger">-<?= number_format($gov_total, 2) ?></th></tr>
        <tr><th colspan="4">الربح الصافي</th><th class="text-success"><?= number_format($net_profit, 2) ?></th></tr>
      </tfoot>
    </table>

    <div id="chart" style="height: 400px"></div>
  </div>

<script>
google.charts.load("current", {packages:["corechart"]});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  const data = google.visualization.arrayToDataTable([
    ['البند', 'الربح'],
    <?php foreach ($profit_data as $name => $data):
      $p = $data['profit'];
      if ($p > 0) echo "['$name', $p],";
    endforeach; ?>
  ]);
  const chart = new google.visualization.PieChart(document.getElementById('chart'));
  chart.draw(data, {
    title: 'توزيع الربح حسب البند',
    is3D: true
  });
}

function exportExcel() {
  var wb = XLSX.utils.table_to_book(document.getElementById('profitTable'), {sheet: "Report"});
  XLSX.writeFile(wb, 'تقرير_الارباح.xlsx');
}


</script>
<script>
document.getElementById('printBtn').addEventListener('click', function() {
  const tableHTML = document.getElementById('profitTable').outerHTML;

  const newWindow = window.open('', '', 'width=900,height=700');
  newWindow.document.write(`
    <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>طباعة تقرير الأرباح</title>
        <style>
          body { font-family: 'Cairo', sans-serif; padding: 30px; font-size: 14px; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
          th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        </style>
      </head>
      <body>
        <h3 style="text-align: center;">📊 تقرير الأرباح</h3>
        ${tableHTML}
        <script>window.onload = function() { window.print(); }<\/script>
      </body>
    </html>
  `);
  newWindow.document.close();
});
</script>
</body>
</html>