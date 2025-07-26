<?php
include '../includes/config.php';

$exam_id = $_GET['exam_id'] ?? 0;

if (!$exam_id) {
    die("رابط غير صحيح.");
}

// 1. جلب بيانات النتيجة مع بيانات المريض واسم المعمل ولوجو من جدول labs بناءً على exam_id
// (لنفترض أن patient_exams يحتوي على lab_id أيضاً)
$stmt = $conn->prepare("SELECT pe.*, p.name AS patient_name, p.gender, p.age_value, p.age_unit, p.code,
                               e.name_en, e.unit, e.normal_range,
                               l.name AS lab_name, l.logo AS lab_logo
                        FROM patient_exams pe
                        JOIN patients p ON pe.patient_id = p.id
                        JOIN exam_catalog e ON pe.exam_id = e.id
                        JOIN labs l ON pe.lab_id = l.id
                        WHERE pe.id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("<div style='padding:20px; font-family:tahoma; text-align:center; color:red;'>⚠️ لا توجد نتيجة. تأكد من صحة الرابط.</div>");
}

function safe($value, $default = 'غير متوفر') {
    return htmlspecialchars($value ?? $default);
}

$print_time = date('H:i d/m/Y');
$base_logo_url = "https://lab.scooly.net/assets/";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تقرير نتيجة فحص - نسخة واتساب</title>
  <style>
    body { font-family: 'Tahoma', sans-serif; padding: 20px 30px; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .logo { width: 110px; height: auto; }
    .center-title { text-align: center; margin: 20px 0; }
    .info { font-size: 15px; margin-top: 10px; }
    .info p { margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 25px; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    .abnormal { color: red; font-weight: bold; }
    .small { font-size: 12px; margin-top: 30px; color: #444; text-align: center; }
  </style>
</head>
<body>

<div class="header">
  <?php if (!empty($data['lab_logo'])): ?>
    <img src="<?= $base_logo_url . safe($data['lab_logo']) ?>" class="logo" alt="Logo" onerror="this.style.display='none'">
  <?php else: ?>
    <div class="logo" style="width:110px;height:60px;border:1px solid #ccc;"></div>
  <?php endif; ?>

  <div style="text-align: center;">
    <h2 style="margin: 0;"><?= safe($data['lab_name']) ?></h2>
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

<div class="small">
  الصفحة 1 من 1
</div>

</body>
</html>
