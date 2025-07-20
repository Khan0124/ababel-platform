
<?php
include 'config.php';

if (isset($_GET['code'])) {
  $code = $_GET['code'];
  $stmt = $conn->prepare("SELECT name FROM clients WHERE code = ?");
  $stmt->bind_param("s", $code);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    echo $row['name'];
  } else {
    echo "";
  }
}
?>
