<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$invoice_id = intval($_GET['id']);

// بيانات المعمل
$lab = $conn->query("SELECT name FROM labs WHERE id = $lab_id")->fetch_assoc();
$lab_name = $lab['name'] ?? 'اسم المعمل';

// بيانات الفاتورة
$stmt = $conn->prepare("SELECT ei.*, p.name AS patient_name, p.code AS patient_code, ic.name AS insurance_name 
                        FROM exam_invoices ei 
                        JOIN patients p ON ei.patient_id = p.id 
                        LEFT JOIN insurance_companies ic ON ei.insurance_company_id = ic.id
                        WHERE ei.id = ? AND ei.lab_id = ?");
$stmt->bind_param("ii", $invoice_id, $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

// الفحوصات المرتبطة
$exams = $conn->query("SELECT ec.name_en, ec.price FROM patient_exams pe 
                       JOIN exam_catalog ec ON pe.exam_id = ec.id
                       WHERE pe.invoice_id = $invoice_id");

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>عرض الفاتورة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { padding: 40px; }
    .logo { width: 120px; }
    .invoice-box { border: 1px solid #ccc; padding: 20px; border-radius: 10px; background: #fdfdfd; }
  </style>
</head>
<body>
  <div class="container invoice-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <img src="../assets/logo.png" class="logo" alt="Logo">
      <h3><?= $lab_name ?></h3>
    </div>

    <h4 class="mb-3">🧾 تفاصيل الفاتورة رقم: <?= $invoice_id ?></h4>
    <p><strong>المريض:</strong> <?= $invoice['patient_name'] ?> (<?= $invoice['patient_code'] ?>)</p>
    <p><strong>التاريخ:</strong> <?= $invoice['invoice_date'] ?></p>
    <p><strong>الجهة المحوِّلة:</strong> <?= $invoice['referred_by'] ?: '—' ?></p>
    <p><strong>شركة التأمين:</strong> <?= $invoice['insurance_name'] ?: '—' ?></p>
    <hr>

    <h5>قائمة الفحوص:</h5>
    <ul>
      <?php while ($e = $exams->fetch_assoc()): ?>
        <li><?= $e['name_en'] ?> - <?= number_format($e['price'], 2) ?> جنيه</li>
      <?php endwhile; ?>
    </ul>
    <hr>
    <p><strong>الإجمالي:</strong> <?= number_format($invoice['total_amount'], 2) ?> جنيه</p>
    <p><strong>الخصم:</strong> <?= number_format($invoice['discount'], 2) ?> جنيه</p>
    <p><strong>المبلغ النهائي:</strong> <?= number_format($invoice['total_amount'] - $invoice['discount'], 2) ?> جنيه</p>

    <div class="text-center mt-4">
      <a href="print_invoice.php?id=<?= $invoice_id ?>" class="btn btn-primary">🖨️ طباعة</a>
      <a href="exams_list.php" class="btn btn-secondary">🔙 الرجوع</a>
    </div>
  </div>
</body>
</html>
