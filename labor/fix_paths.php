<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// حدد مجلد admin داخل نفس المستوى
$dir = __DIR__ . '/admin';

if (!is_dir($dir)) {
    die("❌ المجلد admin غير موجود!");
}

$files = scandir($dir);
foreach ($files as $file) {
    $path = $dir . '/' . $file;

    if (is_file($path) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($path);
        $original = $content;

        // تعديل المسارات
        $content = str_replace("include 'includes/", "include '../includes/", $content);
        $content = str_replace("include \"includes/", "include \"../includes/", $content);
        $content = str_replace("href=\"assets/", "href=\"../assets/", $content);
        $content = str_replace("src=\"assets/", "src=\"../assets/", $content);

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "✔️ تم تعديل: $file<br>";
        }
    }
}
echo "<hr>✅ انتهى تحديث المسارات لجميع ملفات admin.";
?>
