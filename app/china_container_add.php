
<?php
include 'auth.php';
include 'config.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $loading_number = $_POST['loading_number'];
  $container_number = $_POST['container_number'];
  $bill_number = $_POST['bill_number'];
  $category = $_POST['category'];
  $carrier = $_POST['carrier'];
  $registry = $_POST['registry'];
  $weight = $_POST['weight'];
  $expected_arrival = $_POST['expected_arrival'];
  $ship_name = $_POST['ship_name'];
  $custom_station = $_POST['custom_station'];
  $notes = $_POST['notes'];

  $stmt = $conn->prepare("INSERT INTO china_containers
    (loading_number, container_number, bill_number, category, carrier, registry, weight, expected_arrival, ship_name, custom_station, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssssssss", $loading_number, $container_number, $bill_number, $category, $carrier, $registry, $weight, $expected_arrival, $ship_name, $custom_station, $notes);

  if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "⚠️ فشل في إضافة الحاوية.";
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة حاوية - مكتب الصين</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; padding: 30px; }
    form { background: white; max-width: 700px; margin: auto; padding: 20px; border-radius: 10px; }
    label { font-weight: bold; display: block; margin-top: 15px; }
    input, textarea { width: 100%; padding: 10px; margin-top: 5px; }
    button { margin-top: 20px; background: #711739; color: white; border: none; padding: 10px 20px; border-radius: 6px; }
    .error { color: red; text-align: center; margin-top: 10px; }
  </style>
</head>
<body>

<form method="POST">
  <h2 style="text-align:center;">➕ إضافة حاوية - مكتب الصين</h2>

  <label>رقم اللودنق</label>
  <input type="text" name="loading_number" required>

  <label>رقم الحاوية</label>
  <input type="text" name="container_number" required>

  <label>رقم البوليصة</label>
  <input type="text" name="bill_number" required>

  <label>الصنف</label>
  <input type="text" name="category">

  <label>الشركة الناقلة</label>
  <input type="text" name="carrier">

  <label>السجل</label>
  <input type="text" name="registry">

  <label>الوزن</label>
  <input type="text" name="weight">

  <label>تاريخ الوصول المتوقع</label>
  <input type="date" name="expected_arrival">

  <label>الباخرة</label>
  <input type="text" name="ship_name">

  <label>المحطة الجمركية</label>
  <input type="text" name="custom_station">

  <label>ملاحظات</label>
  <textarea name="notes"></textarea>

  <button type="submit">حفظ الحاوية</button>
  <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
  <?php endif; ?>
</form>

</body>
</html>
