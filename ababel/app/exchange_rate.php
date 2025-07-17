
<?php
include 'config.php';

function getCurrentExchangeRate($conn) {
    $query = $conn->query("SELECT exchange_rate FROM settings ORDER BY id DESC LIMIT 1");
    if ($query && $row = $query->fetch_assoc()) {
        return $row['exchange_rate'];
    }
    return null;
}
?>
