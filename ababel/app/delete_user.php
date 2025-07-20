
<?php
include 'auth.php';
include 'config.php';

$id = intval($_GET['id']);

// تحقق من وجود المستخدم
$res = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 الموظف غير موجود.");
}

// تنفيذ الحذف
$conn->query("DELETE FROM users WHERE id = $id");

// العودة إلى صفحة الموظفين
header("Location: users.php");
exit;
?>
