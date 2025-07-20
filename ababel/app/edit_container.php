<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
  die("رقم الحاوية غير محدد.");
}

$id = intval($_GET['id']);

// جلب بيانات الحاوية
$result = $conn->query("SELECT * FROM containers WHERE id = $id");
if ($result->num_rows == 0) {
  die("الحاوية غير موجودة.");
}
$container = $result->fetch_assoc();

// جلب قائمة السجلات
$registers = $conn->query("SELECT id, name FROM registers");

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // قراءة البيانات من الفورم
  $entry_date = $_POST['entry_date'] ?? '';
  $client_code = $_POST['client_code'] ?? '';
  $client_name = $_POST['client_name'] ?? '';
  $loading_number = $_POST['loading_number'] ?? '';
  $carton_count = $_POST['carton_count'] ?? 0;
  $container_number = $_POST['container_number'] ?? '';
  $bill_number = $_POST['bill_number'] ?? '';
  $category = $_POST['category'] ?? '';
  $carrier = $_POST['carrier'] ?? '';
  $registry = $_POST['registry'] ?? null;
  $weight = $_POST['weight'] ?? null;
  $expected_arrival = $_POST['expected_arrival'] ?? '';
  $ship_name = $_POST['ship_name'] ?? '';
  $custom_station = $_POST['custom_station'] ?? '';
  $unloading_place = $_POST['unloading_place'] ?? '';
  $notes = $_POST['notes'] ?? '';
  $release_status = $_POST['release_status'] ?? 'No';
  $company_release = $_POST['company_release'] ?? 'No';
  $office = $_POST['office'] ?? '';
  
  // تحقق من تكرار رقم الحاوية (مع استثناء الحاوية الحالية)
  $stmt = $conn->prepare("SELECT id FROM containers WHERE container_number = ? AND id != ?");
  $stmt->bind_param("si", $container_number, $id);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    $error_msg = "⚠️ رقم الحاوية مستخدم مسبقًا في حاوية أخرى.";
  }
  $stmt->close();

  // تحقق من تكرار رقم اللودنق (مع استثناء الحاوية الحالية)
  if (!$error_msg) {
    $stmt = $conn->prepare("SELECT id FROM containers WHERE loading_number = ? AND id != ?");
    $stmt->bind_param("si", $loading_number, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
      $error_msg = "⚠️ رقم اللودنق مستخدم مسبقًا في حاوية أخرى.";
    }
    $stmt->close();
  }

  // إذا لا يوجد أخطاء نحدث البيانات
  if (!$error_msg) {
    $stmt = $conn->prepare("UPDATE containers SET 
      entry_date=?, code=?, client_name=?, loading_number=?, carton_count=?, container_number=?, 
      bill_number=?, category=?, carrier=?, registry=?, weight=?, expected_arrival=?, 
      ship_name=?, custom_station=?, unloading_place=?, notes=?, release_status=?, company_release=?, office=?
      WHERE id=?");

   $stmt->bind_param(
  "ssssiissssissssssssi", // الآن 20 نوعًا
  $entry_date, $client_code, $client_name, $loading_number, $carton_count,
  $container_number, $bill_number, $category, $carrier, $registry,
  $weight, $expected_arrival, $ship_name, $custom_station, $unloading_place,
  $notes, $release_status, $company_release, $office,
  $id
);


    if ($stmt->execute()) {
      header("Location: containers.php?updated=1");
      exit;
    } else {
      $error_msg = "⚠️ فشل حفظ التعديلات: " . $stmt->error;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تعديل بيانات الحاوية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; }
    .container { background: white; padding: 25px 30px; margin: 30px auto; max-width: 900px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    h4 { margin-bottom: 25px; }
    .form-label { font-weight: 600; }
  </style>
</head>
<body>
  <div class="container">
    <h4 class="text-center">✏️ تعديل بيانات الحاوية</h4>

    <?php if ($error_msg): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">

      <div class="col-md-4">
        <label class="form-label">تاريخ الشحن</label>
        <input type="date" name="entry_date" value="<?= htmlspecialchars($container['entry_date']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">رقم العميل</label>
        <input type="text" name="client_code" value="<?= htmlspecialchars($container['code']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">اسم العميل</label>
        <input type="text" name="client_name" value="<?= htmlspecialchars($container['client_name']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">رقم اللودنق</label>
        <input type="text" name="loading_number" value="<?= htmlspecialchars($container['loading_number']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">عدد الكراتين</label>
        <input type="number" name="carton_count" value="<?= htmlspecialchars($container['carton_count']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">رقم الحاوية</label>
        <input type="text" name="container_number" value="<?= htmlspecialchars($container['container_number']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">رقم البوليصة</label>
        <input type="text" name="bill_number" value="<?= htmlspecialchars($container['bill_number']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">الصنف</label>
        <input type="text" name="category" value="<?= htmlspecialchars($container['category']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">الشركة الناقلة</label>
        <input type="text" name="carrier" value="<?= htmlspecialchars($container['carrier']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">السجل</label>
        <select name="registry" class="form-select">
          <option value="">اختر سجل</option>
          <?php while ($reg = $registers->fetch_assoc()): ?>
            <option value="<?= $reg['id'] ?>" <?= ($reg['id'] == $container['registry']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($reg['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">الوزن</label>
        <input type="text" name="weight" value="<?= htmlspecialchars($container['weight']) ?>" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">تاريخ الوصول المتوقع</label>
        <input type="date" name="expected_arrival" value="<?= htmlspecialchars($container['expected_arrival']) ?>" class="form-control" />
      </div>

      <div class="col-md-6">
        <label class="form-label">اسم الباخرة</label>
        <input type="text" name="ship_name" value="<?= htmlspecialchars($container['ship_name']) ?>" class="form-control" />
      </div>

      <div class="col-md-6">
        <label class="form-label">المحطة الجمركية</label>
        <input type="text" name="custom_station" value="<?= htmlspecialchars($container['custom_station']) ?>" class="form-control" />
      </div>

      <div class="col-md-6">
        <label class="form-label">مكان التفريغ</label>
        <input type="text" name="unloading_place" value="<?= htmlspecialchars($container['unloading_place']) ?>" class="form-control" />
      </div>

      <div class="col-12">
        <label class="form-label">ملاحظات</label>
        <textarea name="notes" rows="2" class="form-control"><?= htmlspecialchars($container['notes']) ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label">تم الإفراج</label>
        <select name="release_status" class="form-select">
          <option value="No" <?= ($container['release_status'] == 'No') ? 'selected' : '' ?>>لا</option>
          <option value="Yes" <?= ($container['release_status'] == 'Yes') ? 'selected' : '' ?>>نعم</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">تم الإفراج من الشركة</label>
        <select name="company_release" class="form-select">
          <option value="No" <?= ($container['company_release'] == 'No') ? 'selected' : '' ?>>لا</option>
          <option value="Yes" <?= ($container['company_release'] == 'Yes') ? 'selected' : '' ?>>نعم</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">اسم المكتب</label>
        <input type="text" name="office" value="<?= htmlspecialchars($container['office']) ?>" class="form-control" />
      </div>

      <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-success px-5">💾 حفظ التعديلات</button>
        <a href="containers.php" class="btn btn-secondary px-4">رجوع</a>
      </div>
    </form>
  </div>
</body>
</html>