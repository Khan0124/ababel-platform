<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'] ?? null;

if (!$lab_id || !isset($_GET['exam_id'])) {
    die("طلب غير صالح.");
}

$exam_id = (int) $_GET['exam_id'];

$stmt = $conn->prepare("
    SELECT pe.*, p.name AS patient_name, p.gender, p.age_value, p.age_unit,
           e.name_en, e.code_exam, e.unit, e.normal_range, e.sample_type, e.description
    FROM patient_exams pe
    JOIN patients p ON pe.patient_id = p.id
    JOIN exam_catalog e ON pe.exam_id = e.id
    WHERE pe.id = ? AND pe.lab_id = ?
");
$stmt->bind_param("ii", $exam_id, $lab_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("<div class='alert alert-danger text-center mt-4'>⚠️ الفحص غير موجود أو لا يتبع هذا المعمل.</div>");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>إدخال نتيجة الفحص</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">📄 إدخال نتيجة فحص - <?= htmlspecialchars($exam['name_en']) ?></h5>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <p><strong>🧍‍♂️ اسم المريض:</strong> <?= htmlspecialchars($exam['patient_name']) ?></p>
          <p><strong>الجنس:</strong> <?= htmlspecialchars($exam['gender']) ?> - <strong>العمر:</strong> <?= $exam['age_value'] . ' ' . $exam['age_unit'] ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>🔬 الفحص:</strong> <?= htmlspecialchars($exam['name_en']) ?> (<?= htmlspecialchars($exam['code_exam']) ?>)</p>
          <p><strong>نوع العينة:</strong> <?= $exam['sample_type'] ?: 'غير محدد' ?></p>
        </div>
      </div>

      <form method="POST" action="save_exam_result.php">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">🔢 النتيجة</label>
            <input type="text" name="value" class="form-control" value="<?= htmlspecialchars($exam['value']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">الوحدة</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($exam['unit']) ?>" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">القيم المرجعية</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($exam['normal_range']) ?>" readonly>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">🗒️ ملاحظات أو تعليق (اختياري)</label>
          <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($exam['comment'] ?? '') ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="exams_list.php" class="btn btn-secondary">⬅️ العودة</a>
          <button type="submit" class="btn btn-success">💾 حفظ النتيجة</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
