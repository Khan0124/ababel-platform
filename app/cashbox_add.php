
<?php
include 'config.php';
include 'auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = $_POST['type'];
  $description = $_POST['description'];
  $method = $_POST['method'];
  $amount = floatval($_POST['amount']);
  $source = $_POST['source'];

  $stmt = $conn->prepare("INSERT INTO cashbox (type, source, description, method, amount) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssd", $type, $source, $description, $method, $amount);
  $stmt->execute();

  header("Location: cashbox.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة عملية للخزنة</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; padding: 30px; }
    form { background: #fff; padding: 20px; max-width: 500px; margin: auto; border-radius: 10px; }
    h2 { text-align: center; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input, select { width: 100%; padding: 10px; margin-top: 5px; }
    button { margin-top: 20px; width: 100%; padding: 10px; background: #711739; color: white; border: none; border-radius: 5px; font-size: 16px; }
  </style>
</head>
<body>
  <form method="POST">
    <h2>إضافة عملية يدويًا للخزنة</h2>
    <label>النوع:</label>
    <select name="type" required>
      <option value="قبض">قبض</option>
      <option value="صرف">صرف</option>
    </select>

    <label>البيان:</label>
    <select name="description" required>
      <option value="سجل">سجل</option>
      <option value="موانئ">موانئ</option>
      <option value="أرضيات">أرضيات</option>
      <option value="تختيم">تختيم</option>
      <option value="أخرى">أخرى</option>
    </select>

    <label>طريقة الدفع:</label>
    <select name="method" required>
      <option value="كاش">كاش</option>
      <option value="بنكك">بنكك</option>
      <option value="أوكاش">أوكاش</option>
      <option value="فوري">فوري</option>
      <option value="شيك">شيك</option>
    </select>

    <label>المبلغ:</label>
    <input type="number" name="amount" step="0.01" required>

    <label>المصدر (اختياري):</label>
    <input type="text" name="source" placeholder="مثلاً: خزنة المكتب">

    <button type="submit">إضافة</button>
  </form>
</body>
</html>
