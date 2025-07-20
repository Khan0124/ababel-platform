<?php
// ababel.net/app/api/china_sync.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable detailed logging
$logFile = __DIR__ . '/sync_debug.log';
function logDebug($message, $data = null) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $log = "[$timestamp] $message";
    if ($data) {
        $log .= " - Data: " . json_encode($data);
    }
    file_put_contents($logFile, $log . "\n", FILE_APPEND);
}

logDebug("Request received", [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders()
]);

// CORS headers
header("Access-Control-Allow-Origin: https://china.ababel.net");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include database connection
include '../config.php';

// API Key validation
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validApiKey = 'AB@1234X-China2Port!';

logDebug("API Key check", ['provided' => $apiKey, 'valid' => $apiKey === $validApiKey]);

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get request method and parse endpoint
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parse the endpoint correctly
$basePath = '/app/api/china_sync.php';
$endpoint = str_replace($basePath, '', $requestUri);
$endpoint = parse_url($endpoint, PHP_URL_PATH) ?: '';

logDebug("Parsed endpoint", ['uri' => $requestUri, 'endpoint' => $endpoint]);

// Parse request body
$input = json_decode(file_get_contents('php://input'), true);
logDebug("Request body", $input);

try {
    switch ($method) {
        case 'POST':
            if (strpos($endpoint, '/containers/create') !== false || $endpoint === '' || $endpoint === '/') {
                // Handle both direct calls and path-based calls
                createContainer($conn, $input['data'] ?? []);
            } elseif (strpos($endpoint, '/containers/update') !== false) {
                updateContainer($conn, $input);
            } elseif (strpos($endpoint, '/containers/update-bol') !== false) {
                updateBol($conn, $input);
            } else {
                throw new Exception("Invalid endpoint: $endpoint");
            }
            break;
            
        case 'DELETE':
            if (strpos($endpoint, '/containers/delete') !== false) {
                deleteContainer($conn, $input);
            } else {
                throw new Exception("Invalid endpoint for DELETE: $endpoint");
            }
            break;
            
        case 'GET':
            if (strpos($endpoint, '/containers/status') !== false) {
                getContainerStatus($conn, $_GET);
            } else {
                throw new Exception("Invalid endpoint for GET: $endpoint");
            }
            break;
            
        default:
            throw new Exception("Method not allowed: $method");
    }
} catch (Exception $e) {
    logDebug("Error occurred", ['message' => $e->getMessage()]);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Create new container from China
 */
function createContainer($conn, $data)
{
    global $logFile;
    logDebug("Creating container", $data);
    
    // Validate required fields
    $required = ['china_loading_id', 'entry_date', 'code', 'client_name', 'loading_number', 'container_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if container already exists for this china_loading_id
    $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $data['china_loading_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        logDebug("Container already exists for china_loading_id", ['id' => $data['china_loading_id']]);
        
        // Return success with existing container info
        $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
        $stmt->bind_param("i", $data['china_loading_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'container_id' => $result['id'],
            'message' => 'Container already exists',
            'existing' => true
        ]);
        return;
    }
    
    // Insert new container with all fields properly mapped
    $sql = "INSERT INTO containers (
                china_loading_id, entry_date, code, client_name, loading_number, 
                loading_no, carton_count, container_number, bill_number, category, 
                carrier, expected_arrival, ship_name, custom_station, office, 
                created_at, seen_by_port, synced, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, 1, 'pending')";
    
    $stmt = $conn->prepare($sql);
    
    // Set default values
    $cartonCount = isset($data['carton_count']) ? intval($data['carton_count']) : 0;
    $billNumber = $data['bill_number'] ?? $data['claim_number'] ?? '';
    $category = $data['category'] ?? 'General Cargo';
    $carrier = $data['carrier'] ?? 'TBD';
    $expectedArrival = $data['expected_arrival'] ?? date('Y-m-d', strtotime('+30 days'));
    $shipName = $data['ship_name'] ?? 'TBD';
    $customStation = $data['custom_station'] ?? 'Port Sudan';
    $office = $data['office'] ?? 'بورتسودان';
    $loadingNo = $data['loading_number']; // Use the same value for both fields
    
    $stmt->bind_param("isssssississsss",
        $data['china_loading_id'],
        $data['entry_date'],
        $data['code'],
        $data['client_name'],
        $data['loading_number'],
        $loadingNo,
        $cartonCount,
        $data['container_number'],
        $billNumber,
        $category,
        $carrier,
        $expectedArrival,
        $shipName,
        $customStation,
        $office
    );
    
    if (!$stmt->execute()) {
        logDebug("Database error", ['error' => $stmt->error]);
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $containerId = $conn->insert_id;
    logDebug("Container created successfully", ['container_id' => $containerId]);
    
    // Create notification
    createContainerNotification($conn, $containerId, 'new_container', 
        "New container arrived from China: {$data['container_number']} for client {$data['client_name']}");
    
    // Log sync
    logSync($conn, 'create_container', $data['china_loading_id'], $containerId, $data, 'success');
    
    echo json_encode([
        'success' => true,
        'container_id' => $containerId,
        'message' => 'Container created successfully'
    ]);
}

/**
 * Update existing container
 */
function updateContainer($conn, $data)
{
    logDebug("Updating container", $data);
    
    if (empty($data['china_loading_id'])) {
        throw new Exception("Missing china_loading_id");
    }
    
    // Find container by china_loading_id
    $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $data['china_loading_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Container not found for china_loading_id: " . $data['china_loading_id']);
    }
    
    $container = $result->fetch_assoc();
    $containerId = $container['id'];
    
    // Build update query
    $updateFields = [];
    $params = [];
    $types = '';
    
    $allowedFields = [
        'entry_date' => 's',
        'client_name' => 's',
        'loading_number' => 's',
        'loading_no' => 's',
        'carton_count' => 'i',
        'container_number' => 's',
        'category' => 's',
        'carrier' => 's',
        'expected_arrival' => 's',
        'ship_name' => 's'
    ];
    
    foreach ($allowedFields as $field => $type) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
            $types .= $type;
        }
    }
    
    // Always update loading_no to match loading_number
    if (isset($data['loading_number']) && !isset($data['loading_no'])) {
        $updateFields[] = "loading_no = ?";
        $params[] = $data['loading_number'];
        $types .= 's';
    }
    
    if (empty($updateFields)) {
        throw new Exception("No valid fields to update");
    }
    
    $sql = "UPDATE containers SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $params[] = $containerId;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    createContainerNotification($conn, $containerId, 'container_updated',
    'Container ' . ($data['container_number'] ?? 'unknown') . ' has been updated from China system');

    
    // Log sync
    logSync($conn, 'update_container', $data['china_loading_id'], $containerId, $data, 'success');
    
    echo json_encode([
        'success' => true,
        'message' => 'Container updated successfully'
    ]);
}

/**
 * Update BOL status
 */
function updateBol($conn, $data)
{
    logDebug("Updating BOL", $data);
    
    if (empty($data['china_loading_id'])) {
        throw new Exception("Missing china_loading_id");
    }
    
    // Find container by china_loading_id
    $stmt = $conn->prepare("SELECT id, container_number FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $data['china_loading_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Container not found");
    }
    
    $container = $result->fetch_assoc();
    $containerId = $container['id'];
    
    // Update BOL status in containers table
    $sql = "UPDATE containers SET bill_of_lading_status = ?, bill_of_lading_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $bolStatus = $data['bill_of_lading_status'] ?? 'issued';
    $bolDate = $data['bill_of_lading_date'] ?? date('Y-m-d');
    
    $stmt->bind_param("ssi", $bolStatus, $bolDate, $containerId);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    // Insert into container_operational_status if BOL is issued
    if ($bolStatus === 'issued') {
        $stmt = $conn->prepare("INSERT INTO container_operational_status (container_id, status, date, created_at) 
                               VALUES (?, 'Bill of Lading Issued', ?, NOW()) 
                               ON DUPLICATE KEY UPDATE date = VALUES(date)");
        $stmt->bind_param("is", $containerId, $bolDate);
        $stmt->execute();
    }
    
    // Create notification
    createContainerNotification($conn, $containerId, 'bol_updated',
        "Bill of Lading status updated for container {$container['container_number']}: {$bolStatus}");
    
    // Log sync
    logSync($conn, 'update_bol', $data['china_loading_id'], $containerId, $data, 'success');
    
    echo json_encode([
        'success' => true,
        'message' => 'BOL status updated successfully'
    ]);
}

/**
 * Delete container
 */
function deleteContainer($conn, $data)
{
    logDebug("Deleting container", $data);
    
    if (empty($data['china_loading_id'])) {
        throw new Exception("Missing china_loading_id");
    }
    
    // Find container by china_loading_id
    $stmt = $conn->prepare("SELECT id, container_number FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $data['china_loading_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Container not found");
    }
    
    $container = $result->fetch_assoc();
    $containerId = $container['id'];
    
    // Delete related records
    $conn->query("DELETE FROM container_notifications WHERE container_id = $containerId");
    $conn->query("DELETE FROM container_operational_status WHERE container_id = $containerId");
    $conn->query("DELETE FROM container_position_history WHERE container_id = $containerId");
    
    // Delete container
    $stmt = $conn->prepare("DELETE FROM containers WHERE id = ?");
    $stmt->bind_param("i", $containerId);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    // Log sync
    logSync($conn, 'delete_container', $data['china_loading_id'], $containerId, $data, 'success');
    
    echo json_encode([
        'success' => true,
        'message' => 'Container deleted successfully'
    ]);
}

/**
 * Get container status
 */
function getContainerStatus($conn, $params)
{
    if (empty($params['china_loading_id'])) {
        throw new Exception("Missing china_loading_id parameter");
    }
    
    $sql = "SELECT 
                c.*,
                (SELECT status FROM container_position_history 
                 WHERE container_id = c.id 
                 ORDER BY created_at DESC 
                 LIMIT 1) as current_position,
                GROUP_CONCAT(DISTINCT cos.status) as operational_statuses
            FROM containers c
            LEFT JOIN container_operational_status cos ON c.id = cos.container_id
            WHERE c.china_loading_id = ?
            GROUP BY c.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $params['china_loading_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Container not found");
    }
    
    $container = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'container' => $container
    ]);
}

/**
 * Create container notification
 */
function createContainerNotification($conn, $containerId, $type, $message)
{
    $stmt = $conn->prepare("INSERT INTO container_notifications (container_id, type, message, created_at) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $containerId, $type, $message);
    $stmt->execute();
}

/**
 * Log synchronization attempt
 */
function logSync($conn, $action, $chinaLoadingId, $containerId, $data, $status)
{
    try {
        $stmt = $conn->prepare("INSERT INTO api_sync_log (
                                   endpoint, method, china_loading_id, container_id, 
                                   request_data, response_code, response_data, 
                                   ip_address, created_at
                               ) VALUES (?, 'POST', ?, ?, ?, 200, ?, ?, NOW())");
        
        $endpoint = "/api/china_sync/$action";
        $requestData = json_encode($data);
        $responseData = json_encode(['status' => $status]);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt->bind_param("siisss", 
            $endpoint, $chinaLoadingId, $containerId, 
            $requestData, $responseData, $ipAddress
        );
        $stmt->execute();
    } catch (Exception $e) {
        logDebug("Failed to log sync", ['error' => $e->getMessage()]);
    }
}