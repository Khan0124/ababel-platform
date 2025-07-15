<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
  die("رقم الحاوية غير محدد.");
}

$id = intval($_GET['id']);

// إذا المكتب بورتسودان، حدث الحاوية كمقروءة
if ($_SESSION['office'] === 'بورتسودان') {
  $conn->query("UPDATE containers SET seen_by_port = 1 WHERE id = $id");
}

// جلب الحاوية مع اسم السجل
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
  'unloading_place' => 'مكان التفريغ',
   'custom_station' => 'المحطة الجمركية',
  
  'notes' => 'ملاحظات',
  'release_status' => 'ريليس',
  'company_release' => 'كومبني ريليس',
  'office' => 'المكتب',
  'seen_by_port' => 'تمت المشاهدة'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تفاصيل الحاوية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h4 class="text-center mb-4">📋 تفاصيل الحاوية</h4>
  <div class="row g-3">
    <?php foreach ($labels as $key => $label): ?>
      <?php if ($key === 'registry'): ?>
        <div class="col-md-4">
          <div class="border rounded p-2 bg-light">
            <strong><?= $label ?>:</strong><br>
            <?= htmlspecialchars($row['registry_name'] ?? '-') ?>
          </div>
        </div>
      <?php elseif (isset($row[$key])): ?>
        <div class="col-md-4">
          <div class="border rounded p-2 bg-light">
            <strong><?= $label ?>:</strong><br>
            <?= htmlspecialchars($row[$key]) ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-4">
    <a href="containers.php" class="btn btn-secondary px-4">🔙 رجوع</a>
  </div>
</div>
</body>
</html>