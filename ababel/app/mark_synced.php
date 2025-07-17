<?php
include 'config.php';
header('Content-Type: text/plain; charset=utf-8');

$table = isset($_GET['table']) ? trim($_GET['table']) : '';
if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo "❌ اسم الجدول غير صحيح: [$table]";
    exit;
}

if ($conn->query("UPDATE `$table` SET synced = 1 WHERE synced = 0")) {
    echo "✅ تم التحديث بنجاح في [$table]";
} else {
    echo "❌ فشل التحديث في [$table]: " . $conn->error;
}
?>
