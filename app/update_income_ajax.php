<?php
include 'auth.php';
include 'config.php';

$id = $_POST['id'] ?? 0;
$description = trim($_POST['description'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);

if (!$id || !$description || $amount <= 0) {
  echo "error"; exit;
}

$update = $conn->query("UPDATE cashbox SET description='$description', amount=$amount WHERE id=$id AND source='يومية قبض'");

echo $update ? "success" : "error";
