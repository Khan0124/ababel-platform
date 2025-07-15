<?php
session_start();
include 'config.php';

if (isset($_SESSION['client_id']) && isset($_POST['new_password'])) {
  $client_id = $_SESSION['client_id'];
  $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
  $stmt = $conn->prepare("UPDATE clients SET password = ? WHERE id = ?");
  $stmt->bind_param("si", $new_pass, $client_id);
  $stmt->execute();
  header("Location: client_dashboard.php?updated=1");
  exit;
}
?>
