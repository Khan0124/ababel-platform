<?php
include 'auth.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: add_container.php");
  exit;
}

// تعقيم وتحديد القيم
$entry_date       = trim($_POST['entry_date'] ?? '');
$code             = trim($_POST['client_code'] ?? '');
$client_name      = trim($_POST['client_name'] ?? '');
$loading_number   = trim($_POST['loading_number'] ?? '');
$unloading_place  = trim($_POST['unloading_place'] ?? '');
$carton_count     = intval($_POST['carton_count'] ?? 0);
$container_number = trim($_POST['container_number'] ?? '');
$bill_number      = trim($_POST['bill_number'] ?? '');
$category         = trim($_POST['category'] ?? '');
$carrier          = trim($_POST['carrier'] ?? '');
$registry         = intval($_POST['registry'] ?? 0);
$weight           = floatval($_POST['weight'] ?? 0);
$expected_arrival = trim($_POST['expected_arrival'] ?? '');
$ship_name        = trim($_POST['ship_name'] ?? '');
$custom_station   = trim($_POST['custom_station'] ?? '');
$notes            = trim($_POST['notes'] ?? '');
$release_status   = in_array($_POST['release_status'] ?? 'No', ['Yes', 'No']) ? $_POST['release_status'] : 'No';
$company_release  = in_array($_POST['company_release'] ?? 'No', ['Yes', 'No']) ? $_POST['company_release'] : 'No';
$office           = $_SESSION['office'] ?? 'غير محدد';

// التحقق من الحقول المطلوبة
if (empty($entry_date) || empty($container_number) || empty($client_name) || empty($code)) {
  header("Location: add_container.php?error=required_fields");
  exit;
}

// تحقق من التكرار: رقم الحاوية
$stmt = $conn->prepare("SELECT id FROM containers WHERE container_number = ?");
$stmt->bind_param("s", $container_number);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
  header("Location: add_container.php?error=duplicate_container");
  exit;
}

// تحقق من التكرار: رقم اللودنق فقط إذا غير فارغ
if (!empty($loading_number)) {
  $stmt2 = $conn->prepare("SELECT id FROM containers WHERE loading_number = ?");
  $stmt2->bind_param("s", $loading_number);
  $stmt2->execute();
  if ($stmt2->get_result()->num_rows > 0) {
    header("Location: add_container.php?error=duplicate_loading");
    exit;
  }
}

// إعداد الإدخال
$stmt = $conn->prepare("INSERT INTO containers (
  entry_date, code, client_name, loading_number, carton_count, container_number, unloading_place,
  bill_number, category, carrier, registry, weight, expected_arrival, ship_name,
  custom_station, notes, release_status, company_release, office
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
  die("فشل في إعداد البيان: " . $conn->error);
}

// bind_param: s=string, i=integer, d=double
$stmt->bind_param(
  "sssisisissidsssssss",
  $entry_date, $code, $client_name, $loading_number, $carton_count, $container_number, $unloading_place,
  $bill_number, $category, $carrier, $registry, $weight, $expected_arrival, $ship_name,
  $custom_station, $notes, $release_status, $company_release, $office
);

if ($stmt->execute()) {
  header("Location: containers.php?added=1");
  exit;
} else {
  die("⚠️ فشل في حفظ البيانات: " . $stmt->error);
}