<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT rr.*, r.name AS register_name 
                        FROM register_requests rr 
                        LEFT JOIN registers r ON rr.register_id = r.id 
                        WHERE rr.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("لم يتم العثور على الطلب.");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طباعة طلب سجل</title>
  <style>
    body { font-family: 'Cairo', sans-serif; direction: rtl; padding: 40px; }
    .header { text-align: center; margin-bottom: 40px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 10px; text-align: right; }
    .logo { float: right; height: 60px; }
    .company-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
    .tax-id { font-size: 14px; }
    .footer { margin-top: 40px; text-align: center; font-size: 14px; }
  </style>
</head>
<body onload="window.print()">

  <div class="header">
    <img src="logo.png" class="logo">
    <div class="company-name">شركة أبابيل للتنمية والاستثمار<br>Ababel Development & Investment Co.</div>
    <div class="tax-id">الرقم الضريبي: 300001127808</div>
    <hr>
    <h3>🧾 إيصال طلب سجل</h3>
  </div>

  <table>
    <tr><th>اسم السجل</th><td><?= htmlspecialchars($data['register_name']) ?></td></tr>
    <tr><th>رقم العميل</th><td><?= htmlspecialchars($data['client_code']) ?></td></tr>
    <tr><th>اسم العميل</th><td><?= htmlspecialchars($data['client_name']) ?></td></tr>
    <tr><th>رقم اللودنق</th><td><?= htmlspecialchars($data['loading_number']) ?></td></tr>
    <tr><th>رقم الحاوية</th><td><?= htmlspecialchars($data['container_number']) ?></td></tr>
    <tr><th>المشتريات</th><td><?= number_format($data['purchase_amount'], 2) ?> ج.س</td></tr>
    <tr><th>قيمة المطالبة</th><td><?= number_format($data['claim_amount'], 2) ?> ج.س</td></tr>
    <tr><th>مكان التفريغ</th><td><?= htmlspecialchars($data['unloading_place']) ?></td></tr>
    <tr><th>رقم المنفستو</th><td><?= htmlspecialchars($data['manifesto_number']) ?></td></tr>
    <tr><th>اسم المرحل</th><td><?= htmlspecialchars($data['transporter_name']) ?></td></tr>
    <tr><th>النولون</th><td><?= number_format($data['transport_fee'], 2) ?> ج.س</td></tr>
    <tr><th>العمولة</th><td><?= number_format($data['commission'], 2) ?> ج.س</td></tr>
    <tr><th>التاريخ</th><td><?= date('Y-m-d', strtotime($data['created_at'])) ?></td></tr>
  </table>

  <div class="footer">
    تمت الطباعة تلقائيًا من نظام أبابيل.
  </div>

</body>
</html>
