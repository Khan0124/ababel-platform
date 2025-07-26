<?php
/**
 * Exams API Routes
 */

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

// Get all exams
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT * FROM exams WHERE lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['data' => $exams]);
    exit;
}

// Get single exam
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $exam_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT * FROM exams WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $exam_id, $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($exam = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode(['data' => $exam]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Exam not found']);
    }
    exit;
}

// Create exam
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $category_id = $data['category_id'] ?? 0;
    $price = $data['price'] ?? 0;
    $normal_range = $data['normal_range'] ?? '';
    $unit = $data['unit'] ?? '';
    
    $sql = "INSERT INTO exams (lab_id, name, category_id, price, normal_range, unit) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isidss", $lab_id, $name, $category_id, $price, $normal_range, $unit);
    
    if ($stmt->execute()) {
        $exam_id = $conn->insert_id;
        header('Content-Type: application/json');
        echo json_encode(['data' => ['id' => $exam_id], 'message' => 'Exam created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create exam']);
    }
    exit;
}

// Update exam
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $exam_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $category_id = $data['category_id'] ?? 0;
    $price = $data['price'] ?? 0;
    $normal_range = $data['normal_range'] ?? '';
    $unit = $data['unit'] ?? '';
    
    $sql = "UPDATE exams SET name = ?, category_id = ?, price = ?, normal_range = ?, unit = ? WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidssii", $name, $category_id, $price, $normal_range, $unit, $exam_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Exam updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Exam not found or no changes made']);
    }
    exit;
}

// Delete exam
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $exam_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "DELETE FROM exams WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $exam_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Exam deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Exam not found']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);