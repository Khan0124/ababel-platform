<?php
session_start();
include '../includes/config.php';

$exam_id = $_GET['exam_id'] ?? 0;
$lab_id = $_SESSION['lab_id'] ?? null;
$employee_name = $_SESSION['employee_name'] ?? null;

$is_employee = $lab_id && $employee_name;

// 1. بيانات المعمل
$lab_info = ['name' => 'المعمل', 'logo' => ''];
if ($lab_id) {
    $lab_stmt = $conn->prepare("SELECT name, logo FROM labs WHERE id = ?");
    $lab_stmt->bind_param("i", $lab_id);
    $lab_stmt->execute();
    $lab_info = $lab_stmt->get_result()->fetch_assoc();
}

// 2. بيانات النتيجة
if ($lab_id) {
    $stmt = $conn->prepare("SELECT pe.*, p.name AS patient_name, p.gender, p.age_value, p.age_unit, p.code,
                                   e.name_en, e.unit, e.normal_range
                            FROM patient_exams pe
                            JOIN patients p ON pe.patient_id = p.id
                            JOIN exam_catalog e ON pe.exam_id = e.id
                            WHERE pe.id = ? AND pe.lab_id = ?");
    $stmt->bind_param("ii", $exam_id, $lab_id);
} else {
    $stmt = $conn->prepare("SELECT pe.*, p.name AS patient_name, p.gender, p.age_value, p.age_unit, p.code,
                                   e.name_en, e.unit, e.normal_range
                            FROM patient_exams pe
                            JOIN patients p ON pe.patient_id = p.id
                            JOIN exam_catalog e ON pe.exam_id = e.id
                            WHERE pe.id = ?");
    $stmt->bind_param("i", $exam_id);
}
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("<div style='padding:20px; font-family:tahoma; text-align:center; color:red;'>⚠️ لا توجد نتيجة. تأكد من صحة الرابط أو أن الفحص لم يتم تسجيله بعد.</div>");
}

$print_time = date('H:i d/m/Y');

// دالة لحماية النصوص
function safe($value, $default = 'غير متوفر') {
    return htmlspecialchars($value ?? $default);
}

// رابط قاعدة اللوغو - غيره لو مسار السيرفر مختلف
$base_logo_url = "https://lab.scooly.net/assets/";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تقرير نتيجة فحص</title>
  <style>
    body { font-family: 'Tahoma', sans-serif; padding: 30px 40px; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .logo { width: 110px; height: auto; }
    .center-title { text-align: center; margin: 20px 0; }
    .info { font-size: 15px; margin-top: 10px; }
    .info p { margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 25px; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    .footer { margin-top: 60px; font-size: 14px; display: flex; justify-content: space-between; }
    .signature { margin-top: 60px; text-align: right; }
    .signature .name { font-weight: bold; }
    .small { font-size: 12px; margin-top: 40px; color: #444; text-align: center; }
    @media print {
      .no-print { display: none; }
      .footer, .signature { page-break-inside: avoid; }
    }
    .abnormal { color: red; font-weight: bold; }
  </style>
</head>
<body>

<?php if ($is_employee): ?>
  <div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn btn-primary">🖨️ طباعة التقرير</button>
  </div>
<?php endif; ?>

<div class="header">
  <?php if (!empty($lab_info['logo'])): ?>
    <?php    $logo_url = 'https://lab.scooly.net/assets/' . $lab_info['logo']; ?> <img src="<?= htmlspecialchars($logo_url) ?>" class="logo" alt="Logo" onerror="this.style.display='none'">
  <?php else: ?>
    <div class="logo" style="width:110px;height:60px;border:1px solid #ccc;"></div>
  <?php endif; ?>

  <div style="text-align: center;">
    <h2 style="margin: 0;"><?= safe($lab_info['name']) ?></h2>
    <h4 style="margin: 0;">Medical Laboratory Report</h4>
  </div>

  <div style="font-size: 14px;">
    <p><strong>التاريخ:</strong> <?= date('d/m/Y') ?></p>
    <p><strong>رقم النتيجة:</strong> <?= safe($data['id']) ?></p>
  </div>
</div>

<div class="center-title">
  <h3>Chemistry Unit / وحدة الكيمياء</h3>
</div>

<div class="info">
  <p><strong>اسم المريض:</strong> <?= safe($data['patient_name']) ?></p>
  <p><strong>الملف:</strong> <?= safe($data['code']) ?> | <strong>العمر:</strong> <?= safe($data['age_value']) . ' ' . safe($data['age_unit']) ?> | <strong>الجنس:</strong> <?= safe($data['gender']) ?></p>
  <p><strong>تاريخ الفحص:</strong> <?= date('d/m/Y', strtotime($data['created_at'])) ?></p>
</div>

<table>
  <thead>
    <tr>
      <th>اسم التحليل</th>
      <th>النتيجة</th>
      <th>الوحدة</th>
      <th>القيمة المرجعية</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?= safe($data['name_en']) ?></td>
      <?php
        $value = $data['value'];
        $range = $data['normal_range'];
        $highlight = '';

        if (!empty($value) && !empty($range) && is_numeric($value) && preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $range, $matches)) {
            $min = floatval($matches[1]);
            $max = floatval($matches[2]);
            if ($value < $min || $value > $max) {
                $highlight = 'class="abnormal"';
            }
        }
      ?>
      <td <?= $highlight ?>><?= safe($value) ?></td>
      <td><?= safe($data['unit']) ?></td>
      <td><?= safe($range) ?></td>
    </tr>
  </tbody>
</table>

<?php if ($is_employee): ?>
  <div class="signature">
    <p class="name">تمت المراجعة بواسطة: <?= safe($employee_name) ?></p>
    <p>التوقيع: ___________________________</p>
  </div>

  <div class="footer">
    <div>تم الطباعة بواسطة: <?= safe($employee_name) ?></div>
    <div>تاريخ الطباعة: <?= $print_time ?></div>
  </div>
<?php else: ?>
  <div class="small">
    تمّت مراجعة التقرير بواسطة المختبر.
  </div>
<?php endif; ?>

<div class="small">
  <img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=<?= urlencode('https://lab.scooly.net/lab/print_result.php?exam_id=' . $exam_id) ?>" width="80"><br>
  الصفحة 1 من 1
</div>

</body>
</html>
