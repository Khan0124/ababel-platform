<?php
include 'auth_check.php';
include '../includes/config.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['lab_id']);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) { die("المريض غير موجود."); }
$patient = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ملف المريض</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-4 text-primary">🗂️ ملف المريض: <?= $patient['name'] ?> (<?= $patient['code'] ?>)</h4>
  <div class="bg-white p-4 rounded shadow-sm">
    <p><strong>الاسم:</strong> <?= $patient['name'] ?></p>
    <p><strong>الجنس:</strong> <?= $patient['gender'] ?></p>
    <p><strong>العمر:</strong> <?= $patient['age_value'] . ' ' . $patient['age_unit'] ?></p>
    <p><strong>الهاتف:</strong> <?= $patient['phone'] ?: '-' ?></p>
    <p><strong>العنوان:</strong> <?= $patient['address'] ?: '-' ?></p>
    <p><strong>التاريخ المرضي:</strong> <?= nl2br($patient['history']) ?: '-' ?></p>
    <p><strong>تاريخ الإضافة:</strong> <?= $patient['created_at'] ?></p>
    <a href="edit_patient.php?id=<?= $patient['id'] ?>" class="btn btn-primary mt-3">✏️ تعديل</a>
    <a href="patients_list.php" class="btn btn-secondary mt-3">🔙 رجوع</a>
  </div>
</div>
</body>
</html>
