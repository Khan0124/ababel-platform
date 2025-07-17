<?php
include 'config.php';

$loading = trim($_GET['loading_number'] ?? '');
$response = ['status' => 'error'];

if ($loading) {
  file_put_contents('log.txt', "✅ استلمنا لودنق: $loading\n", FILE_APPEND);

  // استعلام الحاوية
  $stmt = $conn->prepare("SELECT * FROM containers WHERE loading_number = ?");
  $stmt->bind_param("s", $loading);
  $stmt->execute();
  $result = $stmt->get_result();
  $container = $result->fetch_assoc();

  if ($container) {
    file_put_contents('log.txt', "✅ وجدنا الحاوية: " . json_encode($container) . "\n", FILE_APPEND);

    $client_code = $container['code'];
    $client_name = $container['client_name'];
    $container_id = $container['id'];
    $container_number = $container['container_number'];

    // الجمارك
    $customs = 0;
    $q1 = $conn->prepare("SELECT customs_amount FROM register_requests WHERE container_number = ? ORDER BY id DESC LIMIT 1");
    $q1->bind_param("s", $container_number);
    if ($q1->execute()) {
      $res1 = $q1->get_result()->fetch_assoc();
      $customs = $res1['customs_amount'] ?? 0;
    }

    // المنفستو
    $manifesto = 0;
    $q2 = $conn->prepare("SELECT transport_fee FROM register_requests WHERE container_number = ? ORDER BY id DESC LIMIT 1");
    $q2->bind_param("s", $container_number);
    if ($q2->execute()) {
      $res2 = $q2->get_result()->fetch_assoc();
      $manifesto = $res2['transport_fee'] ?? 0;
    }

    // الموانئ
    $ports = 0;
    $q3 = $conn->prepare("SELECT SUM(amount) as total FROM cashbox WHERE container_id = ? AND description = 'موانئ' AND type = 'صرف'");
    $q3->bind_param("i", $container_id);
    if ($q3->execute()) {
      $res3 = $q3->get_result()->fetch_assoc();
      $ports = $res3['total'] ?? 0;
    }

    // إذن الشركات
    $permission = 0;
    $q4 = $conn->prepare("SELECT SUM(amount) as total FROM cashbox WHERE container_id = ? AND description LIKE '%إذن%' AND type = 'صرف'");
    $q4->bind_param("i", $container_id);
    if ($q4->execute()) {
      $res4 = $q4->get_result()->fetch_assoc();
      $permission = $res4['total'] ?? 0;
    }

    // الأرضيات من cashbox (source فيه كلمة أرض)
    $yard = 0;
    $q5 = $conn->prepare("SELECT SUM(amount) as total FROM cashbox WHERE container_id = ? AND source LIKE '%أرض%' AND type = 'صرف'");
    $q5->bind_param("i", $container_id);
    if ($q5->execute()) {
      $res5 = $q5->get_result()->fetch_assoc();
      $yard = $res5['total'] ?? 0;
    }

    $response = [
      'status' => 'success',
      'client_code' => $client_code,
      'client_name' => $client_name,
      'container_number' => $container_number,
      'customs_amount' => $customs,
      'manifesto_amount' => $manifesto,
      'ports_amount' => $ports,
      'permission_amount' => $permission,
      'yard_amount' => $yard
    ];
  } else {
    file_put_contents('log.txt', "❌ لم يتم العثور على الحاوية لرقم: $loading\n", FILE_APPEND);
    $response = ['status' => 'not_found'];
  }
} else {
  file_put_contents('log.txt', "⚠️ لم يتم إرسال رقم لودنق\n", FILE_APPEND);
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
