<?php
// ababel.net/app/api/receive_loading.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://china.ababel.net');
header('Access-Control-Allow-Methods: POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Signature, X-Timestamp');

// Include necessary files
require_once '../config.php';
require_once '../auth.php';

// API Configuration
$API_KEY = 'secure-api-key-here'; // Should match China system

// Verify API Key
$headers = getallheaders();
$providedApiKey = $headers['X-API-Key'] ?? '';
$signature = $headers['X-Signature'] ?? '';
$timestamp = $headers['X-Timestamp'] ?? '';

if ($providedApiKey !== $API_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid API key']);
    exit;
}

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Verify signature
$expectedSignature = hash_hmac('sha256', $input, $API_KEY);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid signature']);
    exit;
}

// Verify timestamp (prevent replay attacks)
$currentTime = time();
if (abs($currentTime - intval($timestamp)) > 300) { // 5 minutes tolerance
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Request expired']);
    exit;
}

// Process the request based on action
$action = $data['action'] ?? 'create';
$chinaLoadingId = $data['china_loading_id'] ?? null;
$loadingData = $data['data'] ?? [];

try {
    switch ($action) {
        case 'create':
            $result = createContainer($conn, $loadingData, $chinaLoadingId);
            break;
            
        case 'update':
            $result = updateContainer($conn, $loadingData, $chinaLoadingId);
            break;
            
        case 'delete':
            $result = deleteContainer($conn, $chinaLoadingId);
            break;
            
        case 'status':
            $result = updateContainerStatus($conn, $data);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Create new container from China loading
 */
function createContainer($conn, $data, $chinaLoadingId) {
    // Validate required fields
    $required = ['entry_date', 'code', 'client_name', 'loading_number', 'container_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check for duplicate china_loading_id
    $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $chinaLoadingId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Container already exists for this China loading");
    }
    $stmt->close();
    
    // Prepare insert statement
    $sql = "INSERT INTO containers (
        entry_date, code, client_name, loading_number, loading_no,
        carton_count, container_number, bill_number, category,
        carrier, expected_arrival, ship_name, custom_station,
        unloading_place, notes, release_status, company_release,
        office, china_loading_id, synced, seen_by_port
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Set default values
    $data['bill_number'] = $data['bill_number'] ?? '';
    $data['carrier'] = $data['carrier'] ?? '';
    $data['expected_arrival'] = $data['expected_arrival'] ?? date('Y-m-d', strtotime('+30 days'));
    $data['ship_name'] = $data['ship_name'] ?? '';
    $data['custom_station'] = $data['custom_station'] ?? '';
    $data['unloading_place'] = $data['unloading_place'] ?? '';
    $data['release_status'] = $data['release_status'] ?? 'No';
    $data['company_release'] = $data['company_release'] ?? 'No';
    $data['carton_count'] = intval($data['carton_count'] ?? 0);
    $synced = 1;
    $seenByPort = 0;
    
    $stmt->bind_param(
        "sssssississsssssssiii",
        $data['entry_date'],
        $data['code'],
        $data['client_name'],
        $data['loading_number'],
        $data['loading_no'],
        $data['carton_count'],
        $data['container_number'],
        $data['bill_number'],
        $data['category'],
        $data['carrier'],
        $data['expected_arrival'],
        $data['ship_name'],
        $data['custom_station'],
        $data['unloading_place'],
        $data['notes'],
        $data['release_status'],
        $data['company_release'],
        $data['office'],
        $chinaLoadingId,
        $synced,
        $seenByPort
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create container: " . $stmt->error);
    }
    
    $containerId = $stmt->insert_id;
    $stmt->close();
    
    // Create notification for Port Sudan users
    createNotification($conn, $containerId, $data);
    
    return [
        'success' => true,
        'container_id' => $containerId,
        'message' => 'Container created successfully'
    ];
}

/**
 * Update existing container
 */
function updateContainer($conn, $data, $chinaLoadingId) {
    // Find container by china_loading_id
    $stmt = $conn->prepare("SELECT id FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $chinaLoadingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Container not found for China loading ID: $chinaLoadingId");
    }
    
    $container = $result->fetch_assoc();
    $containerId = $container['id'];
    $stmt->close();
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    $types = '';
    
    $allowedFields = [
        'entry_date' => 's',
        'code' => 's',
        'client_name' => 's',
        'loading_number' => 's',
        'loading_no' => 's',
        'carton_count' => 'i',
        'container_number' => 's',
        'category' => 's',
        'notes' => 's'
    ];
    
    foreach ($allowedFields as $field => $type) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
            $types .= $type;
        }
    }
    
    if (empty($updateFields)) {
        throw new Exception("No fields to update");
    }
    
    // Add container ID to params
    $params[] = $containerId;
    $types .= 'i';
    
    $sql = "UPDATE containers SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update container: " . $stmt->error);
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'container_id' => $containerId,
        'message' => 'Container updated successfully'
    ];
}

/**
 * Delete container
 */
function deleteContainer($conn, $chinaLoadingId) {
    // Find and delete container
    $stmt = $conn->prepare("DELETE FROM containers WHERE china_loading_id = ?");
    $stmt->bind_param("i", $chinaLoadingId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete container: " . $stmt->error);
    }
    
    if ($stmt->affected_rows == 0) {
        throw new Exception("Container not found for deletion");
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Container deleted successfully'
    ];
}

/**
 * Update container status
 */
function updateContainerStatus($conn, $data) {
    $chinaLoadingId = $data['china_loading_id'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$chinaLoadingId || !$status) {
        throw new Exception("Missing china_loading_id or status");
    }
    
    // Map status if needed
    $statusMap = [
        'pending' => 'pending',
        'shipped' => 'shipped',
        'arrived' => 'arrived',
        'cleared' => 'cleared',
        'cancelled' => 'cancelled'
    ];
    
    $mappedStatus = $statusMap[$status] ?? $status;
    
    $stmt = $conn->prepare("UPDATE containers SET status = ? WHERE china_loading_id = ?");
    $stmt->bind_param("si", $mappedStatus, $chinaLoadingId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update container status: " . $stmt->error);
    }
    
    if ($stmt->affected_rows == 0) {
        throw new Exception("Container not found for status update");
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Container status updated successfully'
    ];
}

/**
 * Create notification for Port Sudan users
 */
function createNotification($conn, $containerId, $data) {
    $message = sprintf(
        "حاوية جديدة من مكتب الصين: %s - العميل: %s",
        $data['container_number'],
        $data['client_name']
    );
    
    // You can implement a notification system here
    // For now, we'll just mark the container as new (seen_by_port = 0)
    
    // Optional: Send email notification
    // sendEmailNotification($message, $data);
    
    // Optional: Create system notification
    // createSystemNotification($conn, $message, $containerId);
}