<?php
/**
 * ملف الاتصال بقاعدة البيانات – ababel
 * ضعه في مسار خارج جذر الويب إن أمكن ثم استدعِه بـ require_once.
 */

/* 1) إظهار الأخطاء أثناء التطوير فقط */
require_once __DIR__ . '/includes/Translator.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);   // غيّرها إلى 0 في الإنتاج

/* 2) بيانات الاتصال */
$host = '127.0.0.1';            // داخل الـ VPS استخدم localhost دائماً
$db   = 'ababel';
$user = 'ababel';
$pass = 'Khan@70990100';

/* 3) إنشاء الاتصال */
$conn = new mysqli($host, $user, $pass, $db);

/* 4) التحقق من الاتصال */
if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "error"   => "❌ فشل الاتصال بقاعدة البيانات",
        "details" => $conn->connect_error
    ]);
    exit;
}

/* 5) تعيين الترميز لدعم العربية */
$conn->set_charset('utf8mb4');

/* جاهز للاستخدام */
