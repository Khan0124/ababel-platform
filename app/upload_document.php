<?php
include 'auth.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $related_type = $_POST['related_type'];
  $related_id = $_POST['related_id'] ?? null;
  $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
  $uploaded_by = $_SESSION['user_id'] ?? 0;

  // رفع الملف
  if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
    $file_tmp = $_FILES['document']['tmp_name'];
    $file_name = basename($_FILES['document']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (!in_array($file_ext, $allowed)) {
      die("❌ نوع الملف غير مسموح به.");
    }

    $new_name = uniqid('doc_') . '.' . $file_ext;
    $upload_path = 'uploads/' . $new_name;

    if (move_uploaded_file($file_tmp, $upload_path)) {
      $stmt = $conn->prepare("INSERT INTO documents (title, file_path, related_type, related_id, description, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssisi", $title, $new_name, $related_type, $related_id, $description, $uploaded_by);
      $stmt->execute();
      $stmt->close();

      // سجل التعديلات
      if (function_exists('log_activity')) {
        log_activity($uploaded_by, 'إضافة مستند', $related_type, $related_id ?? 0, $title);
      }

      header("Location: documents.php?success=1");
      exit;
    } else {
      die("⚠️ فشل في رفع الملف.");
    }
  } else {
    die("⚠️ لم يتم اختيار أي ملف.");
  }
} else {
  header("Location: documents.php");
  exit;
}
