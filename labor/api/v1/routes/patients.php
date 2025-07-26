<?php
/**
 * Patients API Routes
 */

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

// Get all patients
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT * FROM patients WHERE lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['data' => $patients]);
    exit;
}

// Get single patient
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $patient_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT * FROM patients WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($patient = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode(['data' => $patient]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Patient not found']);
    }
    exit;
}

// Create patient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $dob = $data['dob'] ?? '';
    $gender = $data['gender'] ?? '';
    $address = $data['address'] ?? '';
    
    $sql = "INSERT INTO patients (lab_id, name, phone, email, dob, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $lab_id, $name, $phone, $email, $dob, $gender, $address);
    
    if ($stmt->execute()) {
        $patient_id = $conn->insert_id;
        header('Content-Type: application/json');
        echo json_encode(['data' => ['id' => $patient_id], 'message' => 'Patient created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create patient']);
    }
    exit;
}

// Update patient
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $patient_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $dob = $data['dob'] ?? '';
    $gender = $data['gender'] ?? '';
    $address = $data['address'] ?? '';
    
    $sql = "UPDATE patients SET name = ?, phone = ?, email = ?, dob = ?, gender = ?, address = ? WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $name, $phone, $email, $dob, $gender, $address, $patient_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Patient updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Patient not found or no changes made']);
    }
    exit;
}

// Delete patient
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $lab_id = $_SESSION['lab_id'] ?? null;
    $patient_id = intval($_GET['id']);
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "DELETE FROM patients WHERE id = ? AND lab_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $patient_id, $lab_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Patient deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Patient not found']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);