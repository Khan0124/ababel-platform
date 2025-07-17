
<?php
$folder = __DIR__ . '/../uploads/receipts/';
$days = 30;

foreach (glob($folder . "*") as $file) {
    if (is_file($file) && time() - filemtime($file) >= 60 * 60 * 24 * $days) {
        unlink($file);
    }
}
?>
