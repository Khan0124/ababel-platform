<?php
include 'config.php';

if (!isset($_FILES['sql_file'])) {
  die("❌ لم يتم استلام ملف SQL.");
}

$tmp = $_FILES['sql_file']['tmp_name'];
$sql = file_get_contents($tmp);

// تنفيذ الاستعلامات بدون تكرار
if ($conn->multi_query($sql)) {
  do { $conn->store_result(); } while ($conn->more_results() && $conn->next_result());
  echo "✅ تم استيراد قاعدة البيانات بنجاح.";
} else {
  echo "❌ فشل الاستيراد: " . $conn->error;
}
?>
