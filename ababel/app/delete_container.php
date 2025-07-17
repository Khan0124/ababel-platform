
<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
  die("رقم الحاوية غير محدد.");
}
$id = intval($_GET['id']);

// تأكيد أن الحاوية موجودة
$result = $conn->query("SELECT * FROM containers WHERE id = $id");
if ($result->num_rows == 0) {
  die("⚠️ الحاوية غير موجودة.");
}

// تنفيذ الحذف
$conn->query("DELETE FROM containers WHERE id = $id");

// إعادة التوجيه مع رسالة
header("Location: containers.php?deleted=1");
exit;
?>
