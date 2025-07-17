<?php
header('Content-Type: application/json');
require_once '../config.php';

$clientCode = $_GET['code'] ?? '';

// التحقق من أن الكود غير فارغ
if (empty($clientCode)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'كود العميل مطلوب'
    ]);
    exit;
}

// استعلام معدّل مع التحقق من القيم الفارغة
$query = "SELECT 
  c.container_number,
  COALESCE(cs.status, 'غير معروفة') AS status,
  COALESCE(cs.driver_name, '') AS driver_name,
  COALESCE(cs.driver_phone, '') AS driver_phone,
  COALESCE(r.name, '') AS register_name
FROM containers c
LEFT JOIN container_status cs ON c.id = cs.container_id
LEFT JOIN registers r ON c.register_id = r.id
WHERE c.code = ?
ORDER BY cs.updated_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'فشل في إعداد الاستعلام: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $clientCode);
$stmt->execute();
$result = $stmt->get_result();

$containers = [];
while ($row = $result->fetch_assoc()) {
  $containers[] = $row;
}

// إرجاع النتيجة حتى لو كانت فارغة
echo json_encode([
  'status' => 'success',
  'containers' => $containers
]);
?>