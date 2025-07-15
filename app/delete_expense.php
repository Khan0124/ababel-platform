
<?php
include 'config.php';
include 'auth.php';
$id = intval($_GET['id']);

// تحقق من وجود المصروف قبل الحذف
$res = $conn->query("SELECT * FROM cashbox WHERE id = $id AND type = 'صرف' AND source = 'مصروفات مكتب' LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 المصروف غير موجود.");
}

// تنفيذ الحذف
$conn->query("DELETE FROM cashbox WHERE id = $id");

// الرجوع إلى صفحة المصروفات
header("Location: office_expense.php");
exit;
?>
