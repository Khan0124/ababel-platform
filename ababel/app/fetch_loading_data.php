<?php
include 'config.php';
header('Content-Type: application/json');

$response = ['status' => 'error'];

if (!isset($_GET['loading_number'])) {
  echo json_encode($response);
  exit;
}

$loading_number = $_GET['loading_number'];

// ✅ 0. التحقق إذا اللودنق مستخدم مسبقًا في register_requests
$check_stmt = $conn->prepare("SELECT id FROM register_requests WHERE loading_number = ? LIMIT 1");
$check_stmt->bind_param("s", $loading_number);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
  echo json_encode(['status' => 'exists', 'message' => 'رقم اللودنق مستخدم مسبقًا']);
  exit;
}

// 1. جلب بيانات الحاوية من جدول containers
$stmt = $conn->prepare("SELECT * FROM containers WHERE loading_number = ? LIMIT 1");
$stmt->bind_param("s", $loading_number);
$stmt->execute();
$result = $stmt->get_result();

if (!$container = $result->fetch_assoc()) {
  echo json_encode(['status' => 'error', 'message' => 'لم يتم العثور على الحاوية']);
  exit;
}

$container_id = $container['id'];
$client_code = $container['code'];
$register_id = $container['registry']; // العمود المرتبط بجدول السجلات

// 2. جلب اسم العميل من جدول clients
$client_q = $conn->prepare("SELECT name FROM clients WHERE code = ? LIMIT 1");
$client_q->bind_param("s", $client_code);
$client_q->execute();
$client_result = $client_q->get_result();
$client = $client_result->fetch_assoc();

// 3. جلب اسم السجل من جدول registers
$register_name = '';
if ($register_id) {
  $register_q = $conn->prepare("SELECT name FROM registers WHERE id = ? LIMIT 1");
  $register_q->bind_param("i", $register_id);
  $register_q->execute();
  $register_result = $register_q->get_result();
  $register = $register_result->fetch_assoc();
  $register_name = $register['name'] ?? '';
}

// 4. جلب اسم السائق من container_status
$driver_q = $conn->prepare("SELECT driver_name, driver_phone FROM container_status WHERE container_id = ? ORDER BY id DESC LIMIT 1");
$driver_q->bind_param("i", $container_id);
$driver_q->execute();
$driver_result = $driver_q->get_result();
$driver = $driver_result->fetch_assoc();

// 5. جلب المطالبة من جدول transactions
$claim_q = $conn->prepare("SELECT amount FROM transactions WHERE type = 'مطالبة' AND container_id = ? ORDER BY id DESC LIMIT 1");
$claim_q->bind_param("i", $container_id);
$claim_q->execute();
$claim_result = $claim_q->get_result();
$claim = $claim_result->fetch_assoc();

// 6. بناء الاستجابة النهائية
$response = [
  'status'           => 'success',
  'client_code'      => $client_code,
  'client_name'      => $client['name'] ?? '',
  'register_id'      => $register_id,
  'register_name'    => $register_name,
  'carton_count'     => $container['carton_count'],
  'custom_station'   => $container['custom_station'],
  'category'         => $container['category'],
  'container_number' => $container['container_number'],
  'unloading_place'  => $container['unloading_place'],
  'carrier'          => $container['carrier'],
  'bill_number'      => $container['bill_number'],
  'driver_name'      => $driver['driver_name'] ?? '',
  'driver_phone'     => $driver['driver_phone'] ?? '',
  'claim_amount'     => $claim['amount'] ?? ''
];

echo json_encode($response);
