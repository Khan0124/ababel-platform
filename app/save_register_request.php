<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // استلام البيانات
  $fields = [
    'register_id', 'client_code', 'client_name', 'loading_number', 'carton_count', 'custom_station',
    'category', 'container_number', 'purchase_amount', 'certificate_number', 'customs_amount',
    'claim_amount', 'unloading_place', 'carrier', 'bill_number', 'refund_value', 'refund_type',
    'manifesto_number', 'driver_name', 'driver_phone', 'transporter_name', 'transport_fee', 'commission'
  ];

  $data = [];
  foreach ($fields as $field) {
    $data[$field] = $_POST[$field] ?? null;
  }

  // تحضير جملة الإدخال
$stmt = $conn->prepare("
  INSERT INTO register_requests (
    register_id, client_code, client_name, loading_number, carton_count, custom_station,
    category, container_number, purchase_amount, certificate_number, customs_amount,
    claim_amount, unloading_place, carrier, bill_number, refund_value, refund_type,
    manifesto_number, driver_name, driver_phone, transporter_name, transport_fee, commission
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "isssisssdsddssssssssssd",
  $data['register_id'],        // i
  $data['client_code'],        // s
  $data['client_name'],        // s
  $data['loading_number'],     // s
  $data['carton_count'],       // i
  $data['custom_station'],     // s
  $data['category'],           // s
  $data['container_number'],   // s
  $data['purchase_amount'],    // d
  $data['certificate_number'], // s
  $data['customs_amount'],     // d
  $data['claim_amount'],       // d
  $data['unloading_place'],    // s
  $data['carrier'],            // s
  $data['bill_number'],        // s
  $data['refund_value'],       // s (وليس d لأنك عاملها varchar)
  $data['refund_type'],        // s
  $data['manifesto_number'],   // s
  $data['driver_name'],        // s
  $data['driver_phone'],       // s
  $data['transporter_name'],   // s
  $data['transport_fee'],      // d
  $data['commission']          // d
);


  if ($stmt->execute()) {
    header("Location: register_requests.php?success=1");
    exit;
  } else {
    echo "حدث خطأ في الحفظ: " . $stmt->error;
  }
}
?>
