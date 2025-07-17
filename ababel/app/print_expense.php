
<?php
include 'config.php';
include 'auth.php';
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM cashbox WHERE id = $id AND type = 'صرف' AND source = 'مصروفات مكتب' LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 المصروف غير موجود.");
}
$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إيصال مصروف</title>
  <style>
    body { font-family: 'Cairo', sans-serif; margin: 40px; }
    .receipt {
      max-width: 700px;
      margin: auto;
      padding: 30px;
      border: 2px dashed #333;
    }
    h2, h4 { text-align: center; margin: 0; }
    .info { margin-top: 30px; }
    p { font-size: 18px; margin: 10px 0; }
    .footer { margin-top: 30px; text-align: center; font-size: 16px; }
    .label { display: inline-block; width: 150px; font-weight: bold; }
    .print-btn {
      display: block;
      margin: 20px auto;
      padding: 10px 20px;
      background: #711739;
      color: white;
      border: none;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
    }
    @media print {
      .print-btn { display: none; }
    }
  </style>
</head>
<body>

<div class="receipt">
  <h2>شركة أبابيل للتنمية والاستثمار المحدودة</h2>
  <h4>إيصال مصروف مكتبي</h4>

  <div class="info">
    <p><span class="label">رقم الإيصال:</span> <?php echo $row['id']; ?></p>
    <p><span class="label">التاريخ:</span> <?php echo $row['created_at']; ?></p>
    <p><span class="label">نوع المصروف:</span> <?php echo $row['description']; ?></p>
    <p><span class="label">طريقة الدفع:</span> <?php echo $row['method']; ?></p>
    <p><span class="label">المبلغ:</span> <?php echo number_format($row['amount']); ?> جنيه</p>
    <p><span class="label">ملاحظات:</span> <?php echo $row['notes'] ?: '-'; ?></p>
  </div>

  <div class="footer">
    <p>تم تحرير هذا الإيصال بتاريخ أعلاه لتوثيق صرف المبلغ الموضح.</p>
    <br><br>
    <p>توقيع المدير المالي: ____________________</p>
  </div>
</div>

<button class="print-btn" onclick="window.print()">🖨️ طباعة</button>

</body>
</html>
