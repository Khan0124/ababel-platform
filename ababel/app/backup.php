<?php
$host = 'localhost'; // تأكد من أن المضيف صحيح
$db = 'ababel';
$user = 'ababel';
$pass = 'Khan@70990100';

// الاتصال بقاعدة البيانات
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die('❌ فشل الاتصال بقاعدة البيانات: ' . $mysqli->connect_error);
}

$backup_file = 'ababel_backup_' . date('Ymd_His') . '.sql';
$handle = fopen($backup_file, 'w');

if (!$handle) {
    die('❌ فشل في إنشاء ملف النسخة الاحتياطية.');
}

// الحصول على جميع الجداول
$tables = [];
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// معالجة كل جدول
foreach ($tables as $table) {
    // بنية الجدول
    $create = $mysqli->query("SHOW CREATE TABLE `$table`")->fetch_array();
    fwrite($handle, "--\n-- Table structure for table `$table`\n--\n\n");
    fwrite($handle, $create[1] . ";\n\n");

    // بيانات الجدول
    $res = $mysqli->query("SELECT * FROM `$table`");
    if ($res && $res->num_rows > 0) {
        fwrite($handle, "--\n-- Dumping data for table `$table`\n--\n\n");

        while ($row = $res->fetch_assoc()) {
            $escaped_vals = [];
            foreach ($row as $val) {
                if (is_null($val)) {
                    $escaped_vals[] = 'NULL';
                } else {
                    $escaped_vals[] = "'" . $mysqli->real_escape_string($val) . "'";
                }
            }
            $vals_string = implode(",", $escaped_vals);
            fwrite($handle, "INSERT INTO `$table` VALUES($vals_string);\n");
        }
        fwrite($handle, "\n\n");
    }
}

fclose($handle);

// تسجيل وقت آخر نسخة احتياطية
file_put_contents('last_backup.txt', time());

// تقديم الملف للتحميل
if (file_exists($backup_file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"$backup_file\"");
    header('Content-Length: ' . filesize($backup_file));
    flush();
    readfile($backup_file);
    unlink($backup_file); // حذف النسخة بعد التحميل
    exit;
} else {
    echo "❌ فشل في إنشاء النسخة الاحتياطية.";
}
?>
