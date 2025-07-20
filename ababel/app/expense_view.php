
<?php
include 'config.php';
include 'auth.php';
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM cashbox WHERE id = $id LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 المصروف غير موجود.");
}
$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>عرض مصروف</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; padding: 30px; }
    .box { background: white; padding: 20px; max-width: 600px; margin: auto; border-radius: 10px; border: 1px solid #ddd; }
    h2 { text-align: center; margin-bottom: 20px; }
    p { font-size: 18px; margin: 10px 0; }
    strong { color: #711739; }
    .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #711739; font-weight: bold; }
  </style>
</head>
<body>
  <div class="box">
    <h2>📄 تفاصيل المصروف</h2>
    <p><strong>التاريخ:</strong> <?php echo $row['created_at']; ?></p>
    <p><strong>نوع المصروف:</strong> <?php echo $row['description']; ?></p>
    <p><strong>طريقة الدفع:</strong> <?php echo $row['method']; ?></p>
    <p><strong>المبلغ:</strong> <?php echo number_format($row['amount']); ?> جنيه</p>
    <p><strong>الملاحظات:</strong> <?php echo $row['notes'] ?: '-'; ?></p>
    <a class="back-link" href="office_expense.php">⬅ العودة لمصروفات المكتب</a>
  </div>
</body>
</html>
