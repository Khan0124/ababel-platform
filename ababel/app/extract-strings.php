<?php
$strings = [];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__)
);

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;

    $content = file_get_contents($file);
    preg_match_all('/>([^<]+[^\s])</', $content, $matches);

    foreach ($matches[1] as $text) {
        $text = trim($text);
        if (!empty($text) && preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            $key = md5($text);
            $strings[$key] = $text;
        }
    }
}

// حفظ النصوص في ملف العربية
$output = "<?php\nreturn [\n";
foreach ($strings as $key => $text) {
    $output .= "    '$key' => '" . addslashes($text) . "',\n";
}
$output .= "];";

$savePath = __DIR__ . '/languages/ar/translations.php';
file_put_contents($savePath, $output);

echo "تم استخراج " . count($strings) . " نصاً. قم الآن بترجمة ملف /app/languages/en/translations.php";
