
<?php
if (!isset($_SESSION)) session_start();
include 'config.php';

$unseen = 0;
if ($_SESSION['office'] === 'بورتسودان') {
  $stmt = $conn->query("SELECT COUNT(*) AS total FROM containers WHERE office = 'الصين' AND seen_by_port = 0");
  $row = $stmt->fetch_assoc();
  $unseen = $row['total'];
}

if ($unseen > 0) {
  echo '<a href="containers.php" style="position:relative; display:inline-block; font-size:22px;">
          🔔
          <span style="position:absolute; top:-5px; right:-10px; background:#dc3545; color:#fff; font-size:12px; padding:2px 6px; border-radius:50%;">' . $unseen . '</span>
        </a>';
}
?>
