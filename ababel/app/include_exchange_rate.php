
<?php
include 'config.php';

// جلب سعر الصرف من جدول settings
$rate_result = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
$exchange_rate = $rate_result ? $rate_result->fetch_assoc()['exchange_rate'] : 0;
?>
