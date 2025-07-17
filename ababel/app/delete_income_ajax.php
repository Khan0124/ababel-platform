<?php
include 'auth.php';
include 'config.php';

$id = $_POST['id'] ?? 0;
$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['مدير مكتب', 'مدير عام'])) {
  echo "unauthorized"; exit;
}

$delete = $conn->query("DELETE FROM cashbox WHERE id=$id AND source='يومية قبض'");
echo $delete ? "success" : "error";
