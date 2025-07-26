<?php
// اتصال Labor SaaS
$host = "127.0.0.1";      // استعمل localhost دائماً داخل الـ VPS
$user = "labor";
$pass = "Khan@70990100";
$db   = "labor";  // إذا غيرت اسم القاعدة حدثه هنا

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
