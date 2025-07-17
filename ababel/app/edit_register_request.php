<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? 0;
$data = $conn->query("SELECT * FROM register_requests WHERE id = $id")->fetch_assoc();
if (!$data) die("الطلب غير موجود.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $conn->prepare("UPDATE register_requests SET 
    client_name=?, purchase_amount=?, certificate_number=?, customs_amount=?,
    refund_value=?, refund_type=?, transporter_name=?, transport_fee=?, commission=?
    WHERE id=?");

  $stmt->bind_param(
    "sdsdsssddi",
    $_POST['client_name'],
    $_POST['purchase_amount'],
    $_POST['certificate_number'],
    $_POST['customs_amount'],
    $_POST['refund_value'],
    $_POST['refund_type'],
    $_POST['transporter_name'],
    $_POST['transport_fee'],
    $_POST['commission'],
    $id
  );

  $stmt->execute();
  header("Location: view_register_request.php?id=$id");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل طلب سجل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; padding: 30px; }
    label { font-weight: bold; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="mb-4">✏️ تعديل بيانات طلب السجل</h4>
  <form method="POST" class="row g-3">

    <!-- حقول تلقائية - قراءة فقط -->
    <div class="col-md-6">
      <label>رقم العميل:</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['client_code']) ?>" readonly>
    </div>
    <div class="col-md-6">
      <label>رقم اللودنق:</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['loading_number']) ?>" readonly>
    </div>
    <div class="col-md-6">
      <label>رقم الحاوية:</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['container_number']) ?>" readonly>
    </div>
    <div class="col-md-6">
      <label>قيمة المطالبة:</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['claim_amount']) ?>" readonly>
    </div>

    <!-- حقول قابلة للتعديل -->
    <div class="col-md-6">
      <label>اسم العميل:</label>
      <input type="text" name="client_name" value="<?= htmlspecialchars($data['client_name']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>المشتريات:</label>
      <input type="number" step="0.01" name="purchase_amount" value="<?= htmlspecialchars($data['purchase_amount']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>رقم الشهادة:</label>
      <input type="text" name="certificate_number" value="<?= htmlspecialchars($data['certificate_number']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>مبلغ الجمارك:</label>
      <input type="number" step="0.01" name="customs_amount" value="<?= htmlspecialchars($data['customs_amount']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>قيمة المستردات:</label>
      <input type="number" step="0.01" name="refund_value" value="<?= htmlspecialchars($data['refund_value']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>نوع المستردات:</label>
      <select name="refund_type" class="form-select">
        <option value="">اختر</option>
        <option value="جزء من حاوية" <?= $data['refund_type'] === 'جزء من حاوية' ? 'selected' : '' ?>>جزء من حاوية</option>
        <option value="حاوية كاملة" <?= $data['refund_type'] === 'حاوية كاملة' ? 'selected' : '' ?>>حاوية كاملة</option>
      </select>
    </div>
    <div class="col-md-6">
      <label>اسم المرحل:</label>
      <input type="text" name="transporter_name" value="<?= htmlspecialchars($data['transporter_name']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>النولون:</label>
      <input type="number" step="0.01" name="transport_fee" value="<?= htmlspecialchars($data['transport_fee']) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label>العمولة:</label>
      <input type="number" step="0.01" name="commission" value="<?= htmlspecialchars($data['commission']) ?>" class="form-control">
    </div>

    <div class="col-12 text-center mt-4">
      <button type="submit" class="btn btn-success px-5">💾 حفظ التعديلات</button>
      <a href="view_register_request.php?id=<?= $id ?>" class="btn btn-secondary px-4">رجوع</a>
    </div>
  </form>
</div>
</body>
</html>
