<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
  die("رقم الحاوية غير محدد.");
}
$id = intval($_GET['id']);

$result = $conn->query("SELECT c.*, r.name AS registry_name
                        FROM containers c
                        LEFT JOIN registers r ON c.registry = r.id
                        WHERE c.id = $id");

if ($result->num_rows == 0) {
  die("الحاوية غير موجودة.");
}
$row = $result->fetch_assoc();

$labels = [
  'entry_date' => 'تاريخ الدخول',
  'code' => 'رقم العميل',
  'client_name' => 'اسم العميل',
  'loading_number' => 'رقم اللودنق',
  'carton_count' => 'عدد الكراتين',
  'container_number' => 'رقم الحاوية',
  'bill_number' => 'رقم البوليصة',
  'category' => 'الصنف',
  'carrier' => 'الشركة الناقلة',
  'registry' => 'السجل',
  'weight' => 'الوزن',
  'expected_arrival' => 'تاريخ الوصول',
  'ship_name' => 'اسم الباخرة',
  'custom_station' => 'المحطة الجمركية',
  'notes' => 'ملاحظات',
  'release_status' => 'ريليس',
  'company_release' => 'كومبني ريليس',
  'office' => 'المكتب'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طباعة بيانات الحاوية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none; }
    }
    body { background: #fff; padding: 20px; }
    .container { border: 1px solid #ddd; padding: 20px; border-radius: 10px; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="text-center mb-4">🧾 تقرير بيانات الحاوية</h4>
  <table class="table table-bordered">
    <tbody>
    <?php foreach ($labels as $key => $label): ?>
      <?php if ($key === 'registry'): ?>
        <tr>
          <th><?= $label ?></th>
          <td><?= htmlspecialchars($row['registry_name'] ?? '-') ?></td>
        </tr>
      <?php elseif (isset($row[$key])): ?>
        <tr>
          <th><?= $label ?></th>
          <td><?= htmlspecialchars($row[$key]) ?></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="text-center no-print mt-4">
    <button onclick="window.print()" class="btn btn-primary px-5">🖨️ طباعة</button>
    <a href="containers.php" class="btn btn-secondary px-4">رجوع</a>
  </div>
</div>
</body>
</html>