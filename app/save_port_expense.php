<?php
include 'config.php';
include 'auth.php';

$client_id = $_POST['client_id'];
$code = $_POST['client_code'];
$container_id = $_POST['container_id'];
$rate = $_POST['exchange_rate'];
$items = json_decode($_POST['items_json'], true);
$user_id = $_SESSION['user_id'] ?? 0;

foreach($items as $item) {
  $desc = $conn->real_escape_string($item['desc']);
  $amount = floatval($item['amount']);
  $usd = floatval($item['usd']);

  $conn->query("INSERT INTO cashbox 
    (type, source, description, method, amount, usd, client_id, container_id, user_id) 
    VALUES ('صرف', 'صرف موانئ', '$desc', 'كاش', $amount, $usd, $client_id, $container_id, $user_id)");
}

header("Location: cashbox.php?success=1");
exit;
