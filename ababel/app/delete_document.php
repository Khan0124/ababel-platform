<?php
include 'auth.php';
include 'config.php';

$id = intval($_GET['id'] ?? 0);
$uploaded_by = $_SESSION['user_id'] ?? 0;

// استرجاع الملف أولاً لحذفه من السيرفر
$result = $conn->query("SELECT file_path, related_type, related_id, title FROM documents WHERE id = $id");
if ($result && $row = $result->fetch_assoc()) {
  $file = 'uploads/' . $row['file_path'];

  // حذف من قاعدة البيانات
  $conn->query("DELETE FROM documents WHERE id = $id");

  // حذف الملف من السيرفر
  if (file_exists($file)) {
    unlink($file);
  }

  // سجل التعديلات
  if (function_exists('log_activity')) {
    log_activity($uploaded_by, 'حذف مستند', $row['related_type'], $row['related_id'] ?? 0, $row['title']);
  }

  header("Location: documents.php?deleted=1");
  exit;
} else {
  die("⚠️ مستند غير موجود.");
}
