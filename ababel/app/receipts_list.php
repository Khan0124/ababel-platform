
<?php
include 'config.php';
include 'auth.php';
$res = $conn->query("SELECT t.id, t.serial, t.created_at, c.name, t.amount, t.type FROM transactions t JOIN clients c ON t.client_id = c.id ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>قائمة الإيصالات</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; padding: 20px; }
    table { width: 100%; border-collapse: collapse; background: #fff; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background-color: #711739; color: #fff; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    a { text-decoration: none; color: #711739; font-weight: bold; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h2>🧾 جميع الإيصالات</h2>
  <table>
    <tr><th>#</th><th>التاريخ</th><th>العميل</th><th>النوع</th><th>المبلغ</th><th>عرض</th><th>تعديل</th><th>حذف</th></tr>
    <?php while($row = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo $row['serial']; ?></td>
      <td><?php echo $row['created_at']; ?></td>
      <td><?php echo $row['name']; ?></td>
      <td><?php echo $row['type']; ?></td>
      <td><?php echo number_format($row['amount']); ?></td>
      <td><a href="receipt_view.php?id=<?php echo $row['id']; ?>">عرض</a></td>
      <td><a href="edit_receipt.php?id=<?php echo $row['id']; ?>">تعديل</a></td>
      <td><a href="delete_receipt.php?id=<?php echo $row['id']; ?>" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a></td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
