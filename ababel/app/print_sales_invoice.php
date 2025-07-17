<?php
include 'config.php';
include 'auth.php';

$id = intval($_GET['id'] ?? 0);
$row = $conn->query("SELECT * FROM sales_invoices WHERE id = $id")->fetch_assoc();
if (!$row) die("🚫 الفاتورة غير موجودة");

// جلب اسم المكتب واسم الموظف من الجلسة
$username = $_SESSION['username'] ?? 'الموظف';
$user = $conn->query("SELECT office FROM users WHERE username = '$username'")->fetch_assoc();
$office = $user['office'] ?? 'غير محدد';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🖨 طباعة فاتورة</title>
  <style>
    body { font-family: 'Cairo', sans-serif; direction: rtl; padding: 40px; }
    .invoice-box { max-width: 750px; margin: auto; border: 1px solid #000; padding: 20px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .logo { width: 100px; }
    .company-info { text-align: left; font-size: 14px; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table, td, th { border: 1px solid #000; }
    td, th { padding: 10px; font-size: 15px; }
    .footer { margin-top: 40px; text-align: left; font-size: 14px; }
  </style>
</head>
<body onload="window.print()">

<div class="invoice-box">

  <div class="header">
    <img src="logo.png" class="logo" alt="شعار الشركة"> <!-- تأكد أن logo.png موجود -->
    <div class="company-info">
      <strong>شركة أبابيل للتنمية والاستثمار المحدودة</strong><br>
      Ababeel Development & Investment Co. Ltd<br>
      الرقم الضريبي: 300001127808
    </div>
  </div>

  <hr>

  <table>
    <tr>
      <th>رقم الفاتورة</th>
      <td><?= htmlspecialchars($row['invoice_number']) ?></td>
      <th>تاريخ الفاتورة</th>
      <td><?= $row['invoice_date'] ?></td>
    </tr>
    <tr>
      <th>اسم المشتري</th>
      <td colspan="3"><?= htmlspecialchars($row['buyer_name']) ?></td>
    </tr>
    <tr>
      <th>الصنف</th>
      <td><?= htmlspecialchars($row['item_name']) ?></td>
      <th>عدد الكراتين</th>
      <td><?= $row['carton_count'] ?></td>
    </tr>
    <tr>
      <th>قيمة الفاتورة</th>
      <td><?= number_format($row['invoice_value'], 2) ?> جنيه</td>
      <th>القيمة المضافة</th>
      <td><?= number_format($row['vat_value'], 2) ?> جنيه</td>
    </tr>
  </table>

  <div class="footer">
    الموظف المسؤول: <strong><?= htmlspecialchars($username) ?></strong><br>
    المكتب: <strong><?= htmlspecialchars($office) ?></strong><br><br>
    التوقيع: __________________________
  </div>

</div>

</body>
</html>