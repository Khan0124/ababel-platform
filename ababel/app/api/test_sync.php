<?php
/**
 * Test script to verify China-Port Sudan synchronization
 * Place this file in the China system root directory and access via browser
 */

header('Content-Type: text/html; charset=utf-8');

// Configuration
$chinaDbHost = 'localhost';
$chinaDbName = 'china_ababel';
$chinaDbUser = 'china_ababel';
$chinaDbPass = 'Khan@70990100';

$portSudanApiUrl = 'https://ababel.net/app/api/china_sync.php';
$apiKey = 'AB@1234X-China2Port!';

?>
<!DOCTYPE html>
<html>
<head>
    <title>China-Port Sudan Sync Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        .success { background: #e8f5e9; border-color: #4caf50; }
        .error { background: #ffebee; border-color: #f44336; }
        .info { background: #e3f2fd; border-color: #2196f3; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>China-Port Sudan Synchronization Test</h1>
    
    <div class="test">
        <h2>1. Test API Connectivity</h2>
        <?php
        echo "<p>Testing connection to: $portSudanApiUrl</p>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $portSudanApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<div class='error'>CURL Error: $error</div>";
        } elseif ($httpCode == 200) {
            echo "<div class='success'>✓ API is reachable (HTTP $httpCode)</div>";
        } else {
            echo "<div class='error'>✗ API returned HTTP $httpCode</div>";
        }
        ?>
    </div>
    
    <div class="test">
        <h2>2. Test Create Container</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="test_create">
            <p>This will create a test container in Port Sudan:</p>
            <button type="submit">Run Create Test</button>
        </form>
        
        <?php
        if ($_POST['action'] ?? '' == 'test_create') {
            $testData = [
                'china_loading_id' => 99999,
                'entry_date' => date('Y-m-d'),
                'code' => 'TEST001',
                'client_name' => 'Test Client',
                'loading_number' => 'TEST-' . time(),
                'carton_count' => 100,
                'container_number' => 'TEST' . time(),
                'bill_number' => 'BILL-TEST-001',
                'category' => 'Test Goods',
                'office' => 'بورتسودان'
            ];
            
            echo "<h3>Request Data:</h3>";
            echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $portSudanApiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            echo "<h3>Response:</h3>";
            if ($error) {
                echo "<div class='error'>CURL Error: $error</div>";
            } else {
                echo "<p>HTTP Code: $httpCode</p>";
                $responseData = json_decode($response, true);
                if ($responseData) {
                    echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
                    if ($responseData['success'] ?? false) {
                        echo "<div class='success'>✓ Container created successfully!</div>";
                    } else {
                        echo "<div class='error'>✗ " . ($responseData['error'] ?? 'Unknown error') . "</div>";
                    }
                } else {
                    echo "<pre>$response</pre>";
                }
            }
        }
        ?>
    </div>
    
    <div class="test">
        <h2>3. Check Pending Syncs</h2>
        <?php
        try {
            $conn = new mysqli($chinaDbHost, $chinaDbUser, $chinaDbPass, $chinaDbName);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $sql = "SELECT id, loading_no, container_no, client_name, office, sync_status, sync_attempts 
                    FROM loadings 
                    WHERE office = 'port_sudan' 
                    AND (sync_status = 'pending' OR sync_status = 'failed')
                    ORDER BY created_at DESC 
                    LIMIT 10";
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%'>";
                echo "<tr><th>ID</th><th>Loading No</th><th>Container</th><th>Client</th><th>Status</th><th>Attempts</th><th>Action</th></tr>";
                
                while ($row = $result->fetch_assoc()) {
                    $status = $row['sync_status'] == 'failed' ? 'error' : 'info';
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['loading_no']}</td>";
                    echo "<td>{$row['container_no']}</td>";
                    echo "<td>{$row['client_name']}</td>";
                    echo "<td><span class='$status'>{$row['sync_status']}</span></td>";
                    echo "<td>{$row['sync_attempts']}</td>";
                    echo "<td><form method='post' style='display:inline'>";
                    echo "<input type='hidden' name='action' value='retry_sync'>";
                    echo "<input type='hidden' name='loading_id' value='{$row['id']}'>";
                    echo "<button type='submit'>Retry Sync</button>";
                    echo "</form></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='info'>No pending syncs found</div>";
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <?php
    // Handle retry sync
    if (($_POST['action'] ?? '') == 'retry_sync' && isset($_POST['loading_id'])) {
        echo "<div class='test'>";
        echo "<h2>Retrying Sync for Loading ID: {$_POST['loading_id']}</h2>";
        
        try {
            $conn = new mysqli($chinaDbHost, $chinaDbUser, $chinaDbPass, $chinaDbName);
            $loadingId = intval($_POST['loading_id']);
            
            // Get loading data
            $stmt = $conn->prepare("SELECT * FROM loadings WHERE id = ?");
            $stmt->bind_param("i", $loadingId);
            $stmt->execute();
            $result = $stmt->get_result();
            $loading = $result->fetch_assoc();
            
            if ($loading) {
                // Prepare sync data
                $syncData = [
                    'china_loading_id' => $loading['id'],
                    'entry_date' => $loading['shipping_date'],
                    'code' => $loading['client_code'],
                    'client_name' => $loading['client_name'],
                    'loading_number' => $loading['loading_no'],
                    'carton_count' => $loading['cartons_count'],
                    'container_number' => $loading['container_no'],
                    'bill_number' => $loading['claim_number'] ?? '',
                    'category' => $loading['item_description'] ?: 'General Cargo',
                    'office' => 'بورتسودان'
                ];
                
                echo "<h3>Sync Data:</h3>";
                echo "<pre>" . json_encode($syncData, JSON_PRETTY_PRINT) . "</pre>";
                
                // Send to Port Sudan
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $portSudanApiUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($syncData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKey
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "<h3>Response:</h3>";
                $responseData = json_decode($response, true);
                echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
                
                if ($responseData['success'] ?? false) {
                    // Update sync status
                    $updateStmt = $conn->prepare("UPDATE loadings SET sync_status = 'synced', last_sync_at = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $loadingId);
                    $updateStmt->execute();
                    
                    echo "<div class='success'>✓ Sync successful!</div>";
                } else {
                    // Update sync attempts
                    $updateStmt = $conn->prepare("UPDATE loadings SET sync_attempts = sync_attempts + 1 WHERE id = ?");
                    $updateStmt->bind_param("i", $loadingId);
                    $updateStmt->execute();
                    
                    echo "<div class='error'>✗ Sync failed: " . ($responseData['error'] ?? 'Unknown error') . "</div>";
                }
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
        
        echo "</div>";
    }
    ?>
    
    <div class="test">
        <h2>4. Recent Sync Logs</h2>
        <?php
        try {
            $conn = new mysqli($chinaDbHost, $chinaDbUser, $chinaDbPass, $chinaDbName);
            
            // Check if api_sync_log table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'api_sync_log'");
            if ($tableCheck->num_rows > 0) {
                $sql = "SELECT * FROM api_sync_log ORDER BY created_at DESC LIMIT 10";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%'>";
                    echo "<tr><th>ID</th><th>Endpoint</th><th>China ID</th><th>Response Code</th><th>Created At</th><th>Details</th></tr>";
                    
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = ($row['response_code'] >= 200 && $row['response_code'] < 300) ? 'success' : 'error';
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['endpoint']}</td>";
                        echo "<td>{$row['china_loading_id']}</td>";
                        echo "<td class='$statusClass'>{$row['response_code']}</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "<td><button onclick='toggleDetails({$row['id']})'>View</button></td>";
                        echo "</tr>";
                        echo "<tr id='details_{$row['id']}' style='display:none'>";
                        echo "<td colspan='6'>";
                        echo "<strong>Request:</strong><pre>" . json_encode(json_decode($row['request_data']), JSON_PRETTY_PRINT) . "</pre>";
                        echo "<strong>Response:</strong><pre>" . json_encode(json_decode($row['response_data']), JSON_PRETTY_PRINT) . "</pre>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='info'>No sync logs found</div>";
                }
            } else {
                echo "<div class='info'>api_sync_log table not found in China database</div>";
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo "<div class='error'>Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <script>
    function toggleDetails(id) {
        var row = document.getElementById('details_' + id);
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }
    </script>
</body>
</html>