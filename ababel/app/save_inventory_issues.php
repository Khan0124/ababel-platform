<?php
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lost_type = $_POST['lost_type'];
  $lost_quantity = $_POST['lost_quantity'];
  $lost_value = $_POST['lost_value'];

  $damaged_type = $_POST['damaged_type'];
  $damaged_quantity = $_POST['damaged_quantity'];
  $damaged_value = $_POST['damaged_value'];

  $stmt = $conn->prepare("INSERT INTO inventory_issues 
    (lost_type, lost_quantity, lost_value, damaged_type, damaged_quantity, damaged_value) 
    VALUES (?, ?, ?, ?, ?, ?)");

  $stmt->bind_param("sidsid", 
    $lost_type, 
    $lost_quantity, 
    $lost_value, 
    $damaged_type, 
    $damaged_quantity, 
    $damaged_value
  );

  if ($stmt->execute()) {
    header("Location: inventory_issues.php?success=1");
    exit;
  } else {
    die("فشل في حفظ البيانات.");
  }
}
