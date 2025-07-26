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

// الفحوصات
$exams = $conn->query("SELECT ec.name_en, ec.price FROM patient_exams pe 
                       JOIN exam_catalog ec ON pe.exam_id = ec.id
                       WHERE pe.invoice_id = $invoice_id");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طباعة الفاتورة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { padding: 40px; font-family: 'Arial', sans-serif; }
    .invoice-box { border: 1px solid #ccc; padding: 20px; border-radius: 10px; }
    .logo { width: 100px; float: left; }
    .header-title { text-align: center; margin-bottom: 20px; }
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body>
<div class="container invoice-box">
  <div class="d-flex justify-content-between align-items-center">
    <img src="../assets/logo.png" class="logo" alt="Logo">
    <h4 class="text-end"><?= $lab_name ?></h4>
  </div>

  <hr>
  <div class="header-title">
    <h5>🔖 فاتورة فحوصات</h5>
    <p>رقم الفاتورة: <?= $invoice_id ?></p>
  </div>

  <p><strong>المريض:</strong> <?= $invoice['patient_name'] ?> (<?= $invoice['patient_code'] ?>)</p>
  <p><strong>التاريخ:</strong> <?= $invoice['invoice_date'] ?></p>
  <p><strong>الجهة المحوِّلة:</strong> <?= $invoice['referred_by'] ?: '—' ?></p>
  <p><strong>شركة التأمين:</strong> <?= $invoice['insurance_name'] ?: '—' ?></p>

  <hr>
  <h6>🧪 الفحوص:</h6>
  <ul>
    <?php while ($e = $exams->fetch_assoc()): ?>
      <li><?= $e['name_en'] ?> - <?= number_format($e['price'], 2) ?> جنيه</li>
    <?php endwhile; ?>
  </ul>

  <hr>
  <p><strong>الإجمالي:</strong> <?= number_format($invoice['total_amount'], 2) ?> جنيه</p>
  <p><strong>الخصم:</strong> <?= number_format($invoice['discount'], 2) ?> جنيه</p>
  <p><strong>المبلغ النهائي:</strong> <?= number_format($invoice['total_amount'] - $invoice['discount'], 2) ?> جنيه</p>

  <div class="text-center no-print mt-4">
    <button class="btn btn-primary" onclick="window.print()">🖨️ طباعة</button>
    <a href="view_invoice.php?id=<?= $invoice_id ?>" class="btn btn-secondary">رجوع</a>
  </div>
</div>
</body>
</html>
