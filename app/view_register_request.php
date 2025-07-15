<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT rr.*, r.name AS register_name FROM register_requests rr LEFT JOIN registers r ON rr.register_id = r.id WHERE rr.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) die("الطلب غير موجود.");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>عرض طلب سجل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 30px; }
    table { background: white; width: 100%; border: 1px solid #ddd; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: right; }
    th { background-color: #eee; width: 25%; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="mb-4">📄 تفاصيل طلب السجل</h4>
  <table class="table table-bordered">
    <tr><th>اسم السجل</th><td><?= htmlspecialchars($data['register_name']) ?></td></tr>
    <tr><th>رقم العميل</th><td><?= htmlspecialchars($data['client_code']) ?></td></tr>
    <tr><th>اسم العميل</th><td><?= htmlspecialchars($data['client_name']) ?></td></tr>
    <tr><th>رقم اللودنق</th><td><?= htmlspecialchars($data['loading_number']) ?></td></tr>
    <tr><th>عدد الكراتين</th><td><?= htmlspecialchars($data['carton_count']) ?></td></tr>
    <tr><th>المحطة الجمركية</th><td><?= htmlspecialchars($data['custom_station']) ?></td></tr>
    <tr><th>نوع البضاعة</th><td><?= htmlspecialchars($data['category']) ?></td></tr>
    <tr><th>رقم الحاوية</th><td><?= htmlspecialchars($data['container_number']) ?></td></tr>
    <tr><th>المشتريات</th><td><?= htmlspecialchars($data['purchase_amount']) ?></td></tr>
    <tr><th>رقم الشهادة</th><td><?= htmlspecialchars($data['certificate_number']) ?></td></tr>
    <tr><th>مبلغ الجمارك</th><td><?= htmlspecialchars($data['customs_amount']) ?></td></tr>
    <tr><th>قيمة المطالبة</th><td><?= htmlspecialchars($data['claim_amount']) ?></td></tr>
    <tr><th>مكان التفريغ</th><td><?= htmlspecialchars($data['unloading_place']) ?></td></tr>
    <tr><th>الشركة الناقلة</th><td><?= htmlspecialchars($data['carrier']) ?></td></tr>
    <tr><th>رقم البوليصة</th><td><?= htmlspecialchars($data['bill_number']) ?></td></tr>
    <tr><th>قيمة المستردات</th><td><?= htmlspecialchars($data['refund_value']) ?></td></tr>
    <tr><th>نوع المستردات</th><td><?= htmlspecialchars($data['refund_type']) ?></td></tr>
    <tr><th>رقم المنفستو</th><td><?= htmlspecialchars($data['manifesto_number']) ?></td></tr>
    <tr><th>اسم السائق</th><td><?= htmlspecialchars($data['driver_name']) ?></td></tr>
    <tr><th>رقم السائق</th><td><?= htmlspecialchars($data['driver_phone']) ?></td></tr>
    <tr><th>اسم المرحل</th><td><?= htmlspecialchars($data['transporter_name']) ?></td></tr>
    <tr><th>النولون</th><td><?= htmlspecialchars($data['transport_fee']) ?></td></tr>
    <tr><th>العمولة</th><td><?= htmlspecialchars($data['commission']) ?></td></tr>
    <tr><th>تاريخ الطلب</th><td><?= htmlspecialchars($data['created_at']) ?></td></tr>
  </table>

  <div class="text-center mt-4">
    <a href="register_requests_list.php" class="btn btn-secondary">🔙 رجوع</a>
    <a href="edit_register_request.php?id=<?= $data['id'] ?>" class="btn btn-warning">✏️ تعديل</a>
    <a href="print_register_request.php?id=<?= $data['id'] ?>" class="btn btn-info" target="_blank">🖨️ طباعة</a>
  </div>
</div>
</body>
</html>
