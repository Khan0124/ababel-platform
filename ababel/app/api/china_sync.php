<?php
// ababel.net/app/api/china_sync.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$validApiKey = 'your-secure-api-key-here'; // Should be stored in config

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/app/api/china_sync.php', '', $request);

// Parse request body
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            if (strpos($endpoint, '/containers/create') !== false) {
                createContainer($conn, $input);
            } elseif (strpos($endpoint, '/containers/update') !== false) {
                updateContainer($conn, $input);
            } elseif (strpos($endpoint, '/containers/update-bol') !== false) {
                updateBol($conn, $input);
            } else {
                throw new Exception('Invalid endpoint');
            }
            break;
            
        case 'DELETE':
            if (strpos($endpoint, '/containers/delete') !== false) {
                deleteContainer($conn, $input);
            } else {
                throw new Exception('Invalid endpoint');
            }
            break;
            
        case 'GET':
            if (strpos($endpoint, '/containers/status') !== false) {
                getContainerStatus($conn, $_GET);
            } else {
                throw new Exception('Invalid endpoint');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Create new container from China
 */
function createContainer($conn, $data)
{
    // Validate required fields
    $required = ['china_loading_id', 'entry_date', 'code', 'client_name', 'loading_number', 'container_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check for duplicate loading number
    $stmt = $conn->prepare("SELECT id FROM containers WHERE loading_number = ?");
    $stmt->bind_param("s", $data['loading_number']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Loading number already exists");
    }
    
    // Insert new container
    $sql = "INSERT INTO containers (
                china_loading_id, entry_date, code, client_name, loading_number, 
                carton_count, container_number, bill_number, category, carrier,
                expected_arrival, ship_name, custom_station, office, 
                created_at, seen_by_port, synced
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssissssssss",
        $data['china_loading_id'],
        $data['entry_date'],
        $data['code'],
        $data['client_name'],
        $data['loading_number'],
        $data['carton_count'] ?? 0,
        $data['container_number'],
        $data['bill_number'] ?? '',
        $data['category'] ?? 'General Cargo',
        $data['carrier'] ?? 'TBD',
        $data['expected_arrival'] ?? date('Y-m-d', strtotime('+30 days')),
        $data['ship_name'] ?? 'TBD',
        $data['custom_station'] ?? 'Port Sudan',
        $data['office'] ?? 'بورتسودان'
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $containerId = $conn->insert_id;
    
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
    if (empty($data['china_loading_id'])) {
        throw new Exception("Missing china_loading_id");
    }
    
    // Find container by china_loading_id
    $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $data['china_loading_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Container not found");
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
    
    // Create notification
    createContainerNotification($conn, $containerId, 'container_updated',
        "Container {$data['container_number'] ?? 'unknown'} has been updated from China system");
    
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
    $stmt->bind_param("ssi", 
        $data['bill_of_lading_status'],
        $data['bill_of_lading_date'] ?? date('Y-m-d'),
        $containerId
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    // Insert into container_operational_status if BOL is issued
    if ($data['bill_of_lading_status'] === 'issued') {
        $stmt = $conn->prepare("INSERT INTO container_operational_status (container_id, status, date, created_at) 
                               VALUES (?, 'Bill of Lading Issued', ?, NOW()) 
                               ON DUPLICATE KEY UPDATE date = VALUES(date)");
        $stmt->bind_param("is", $containerId, $data['bill_of_lading_date'] ?? date('Y-m-d'));
        $stmt->execute();
    }
    
    // Handle file sync if provided
    if (!empty($data['bill_of_lading_file'])) {
        // Note: File handling would need additional implementation for file transfer
        $sql = "UPDATE containers SET bill_of_lading_file = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $data['bill_of_lading_file'], $containerId);
        $stmt->execute();
    }
    
    // Create notification
    createContainerNotification($conn, $containerId, 'bol_updated',
        "Bill of Lading status updated for container {$container['container_number']}: {$data['bill_of_lading_status']}");
    
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
}

/**
 * Webhook endpoint for Port Sudan to notify China of local changes
 */
function sendWebhookToChina($action, $data)
{
    $webhookUrl = 'https://china.ababel.net/api/port-sudan-webhook';
    
    $payload = json_encode([
        'action' => $action,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s'),
        'source' => 'port_sudan'
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: your-webhook-key'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response];
}