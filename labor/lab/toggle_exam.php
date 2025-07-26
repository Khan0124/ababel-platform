<?php
ob_start();
session_start();
include 'auth_employee.php';
include '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method."
    ]);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? intval($_POST['status']) : 0;
$lab_id = $_SESSION['lab_id'] ?? 0;

if ($id <= 0 || $lab_id <= 0 || !in_array($status, [0, 1])) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Invalid input parameters: ID=$id, Status=$status, Lab_ID=$lab_id."
    ]);
    exit;
}

$check_stmt = $conn->prepare("SELECT is_active FROM exam_catalog WHERE id = ? AND lab_id = ?");
if (!$check_stmt) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Database error: Failed to prepare check statement. " . $conn->error
    ]);
    exit;
}

$check_stmt->bind_param("ii", $id, $lab_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Exam with ID=$id does not exist or does not belong to lab ID=$lab_id."
    ]);
    $check_stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$current_status = $row['is_active'];
$check_stmt->close();

if ($current_status === $status) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "No update needed: Exam is already " . ($status ? "active" : "inactive") . "."
    ]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("UPDATE exam_catalog SET is_active = ? WHERE id = ? AND lab_id = ?");
if (!$stmt) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Database error: Failed to prepare update statement. " . $conn->error
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("iii", $status, $id, $lab_id);
$success = $stmt->execute();

if ($success && $stmt->affected_rows > 0) {
    ob_clean();
    echo json_encode([
        "success" => true,
        "new_status" => $status
    ]);
} else {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => "Failed to update exam status: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
exit;
?>