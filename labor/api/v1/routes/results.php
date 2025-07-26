<?php
/**
 * Results API Routes
 */

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

// Get all results
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT r.*, p.name as patient_name, e.name as exam_name 
            FROM results r
            JOIN patients p ON r.patient_id = p.id
            JOIN exams e ON r.exam_id = e.id
            WHERE p.lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['data' => $results]);
    exit;
}

// Get single result
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $result_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT r.*, p.name as patient_name, e.name as exam_name 
            FROM results r
            JOIN patients p ON r.patient_id = p.id
            JOIN exams e ON r.exam_id = e.id
            WHERE r.id = ? AND p.lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $result_id, $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($data = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Result not found']);
    }
    exit;
}

// Create result
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $patient_id = $data['patient_id'] ?? 0;
    $exam_id = $data['exam_id'] ?? 0;
    $result_value = $data['result'] ?? '';
    $notes = $data['notes'] ?? '';
    $performed_by = $_SESSION['employee_id'] ?? 0;
    
    // Verify patient belongs to this lab
    $sql = "SELECT id FROM patients WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $lab_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid patient']);
        exit;
    }
    
    $sql = "INSERT INTO results (patient_id, exam_id, result, notes, performed_by, performed_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissi", $patient_id, $exam_id, $result_value, $notes, $performed_by);
    
    if ($stmt->execute()) {
        $result_id = $conn->insert_id;
        header('Content-Type: application/json');
        echo json_encode(['data' => ['id' => $result_id], 'message' => 'Result created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create result']);
    }
    exit;
}

// Update result
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $result_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $result_value = $data['result'] ?? '';
    $notes = $data['notes'] ?? '';
    
    $sql = "UPDATE results r 
            JOIN patients p ON r.patient_id = p.id 
            SET r.result = ?, r.notes = ? 
            WHERE r.id = ? AND p.lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $result_value, $notes, $result_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Result updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Result not found or no changes made']);
    }
    exit;
}

// Delete result
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $result_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "DELETE r FROM results r 
            JOIN patients p ON r.patient_id = p.id 
            WHERE r.id = ? AND p.lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $result_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Result deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Result not found']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);