<?php
include 'config.php';
include 'auth.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
  $stmt = $conn->prepare("DELETE FROM register_requests WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
}

// إعادة التوجيه إلى القائمة بعد الحذف
header("Location: register_requests_list.php");
exit;
