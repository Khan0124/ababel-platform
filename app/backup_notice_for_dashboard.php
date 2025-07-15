<?php
$backup_notice = '';

// قراءة توقيتات النسخ والتعديلات (كأرقام Unix)
$last_backup = file_exists('last_backup.txt') ? intval(file_get_contents('last_backup.txt')) : 0;
$last_change = file_exists('last_change.txt') ? intval(file_get_contents('last_change.txt')) : 0;

$now = time();
$day_seconds = 86400;

// منطق التنبيه
if ($last_backup == 0) {
  $backup_notice = "<div class='alert alert-warning text-center'>⚠️ لم يتم إنشاء أي نسخة احتياطية بعد! <a href='backup.php' class='btn btn-sm btn-outline-primary ms-2'>📥 إنشاء أول نسخة الآن</a></div>";
} elseif (($now - $last_backup) >= $day_seconds) {
  $days = floor(($now - $last_backup) / $day_seconds);
  $backup_notice = "<div class='alert alert-warning text-center'>⚠️ مر <strong>$days يومًا</strong> منذ آخر نسخة احتياطية. <a href='backup.php' class='btn btn-sm btn-outline-primary ms-2'>📥 إنشاء نسخة الآن</a></div>";
} elseif ($last_change > $last_backup) {
  $backup_notice = "<div class='alert alert-info text-center'>📦 تم تعديل بيانات بعد آخر نسخة. يُفضل إنشاء نسخة جديدة. <a href='backup.php' class='btn btn-sm btn-outline-primary ms-2'>📥 نسخة جديدة</a></div>";
}

// عرض التنبيه (إن وجد)
echo $backup_notice;
?>
