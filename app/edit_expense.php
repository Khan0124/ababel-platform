
<?php
include 'config.php';
include 'auth.php';
$id = intval($_GET['id']);

// جلب البيانات الحالية
$res = $conn->query("SELECT * FROM cashbox WHERE id = $id AND type = 'صرف' AND source = 'مصروفات مكتب' LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 المصروف غير موجود.");
}
$row = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $description = $_POST['description'];
  $method = $_POST['method'];
  $amount = floatval($_POST['amount']);
  $notes = $_POST['notes'];

  $stmt = $conn->prepare("UPDATE cashbox SET description = ?, method = ?, amount = ?, notes = ? WHERE id = ?");
  $stmt->bind_param("ssdsi", $description, $method, $amount, $notes, $id);
  $stmt->execute();

  header("Location: expense_view.php?id=$id");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل مصروف</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f2f2f2; padding: 30px; }
    form { background: #fff; padding: 20px; max-width: 500px; margin: auto; border-radius: 10px; }
    h2 { text-align: center; margin-bottom: 20px; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    select, input, textarea { width: 100%; padding: 10px; margin-top: 5px; }
    button { margin-top: 20px; width: 100%; padding: 10px; background: #711739; color: white; border: none; border-radius: 5px; font-size: 16px; }
  </style>
</head>
<body>

<form method="POST">
  <h2>✏️ تعديل مصروف</h2>

  <label>نوع المصروف:</label>
  <select name="description" required>
    <?php
      $options = ["حركة", "أدوات مكتبية", "كهرباء", "مياه", "صيانة", "اتصالات", "ضيافة", "مرتبات", "حوافز", "إكراميات", "إيجارات", "أصول", "أخرى"];
      foreach ($options as $option) {
        $selected = $row['description'] === $option ? 'selected' : '';
        echo "<option value='$option' $selected>$option</option>";
      }
    ?>
  </select>

  <label>طريقة الدفع:</label>
  <select name="method" required>
    <?php
      $methods = ["كاش", "بنكك", "أوكاش", "فوري", "شيك"];
      foreach ($methods as $method) {
        $selected = $row['method'] === $method ? 'selected' : '';
        echo "<option value='$method' $selected>$method</option>";
      }
    ?>
  </select>

  <label>المبلغ:</label>
  <input type="number" name="amount" step="0.01" value="<?php echo $row['amount']; ?>" required>

  <label>ملاحظات:</label>
  <textarea name="notes" rows="3"><?php echo $row['notes']; ?></textarea>

  <button type="submit">💾 حفظ التعديلات</button>
</form>

</body>
</html>
