
<?php
session_start();
include 'config.php';

echo "<h3>اختبار الجلسة والإشعارات</h3>";
echo "<pre>";
echo "اسم المكتب في الجلسة: " . ($_SESSION['office'] ?? 'غير محدد') . "\n";

if ($_SESSION['office'] === 'بورتسودان') {
  $res = $conn->query("SELECT COUNT(*) as total FROM containers WHERE office = 'الصين' AND seen_by_port = 0");
  $row = $res->fetch_assoc();
  echo "عدد الحاويات الجديدة من الصين (غير مقروءة): " . $row['total'];
} else {
  echo "الموظف الحالي ليس من مكتب بورتسودان.";
}
echo "</pre>";
?>
