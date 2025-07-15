
<?php
include 'config.php';

if (isset($_GET['loading_number'])) {
  $loading_number = $_GET['loading_number'];

  $stmt = $conn->prepare("SELECT * FROM china_containers WHERE loading_number = ?");
  $stmt->bind_param("s", $loading_number);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    echo json_encode([
      'container_number' => $row['container_number'],
      'bill_number' => $row['bill_number'],
      'category' => $row['category'],
      'carrier' => $row['carrier'],
      'registry' => $row['registry'],
      'weight' => $row['weight'],
      'expected_arrival' => $row['expected_arrival'],
      'ship_name' => $row['ship_name'],
      'custom_station' => $row['custom_station'],
      'notes' => $row['notes']
    ]);
  } else {
    echo json_encode(null);
  }
}
?>
