<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// Get statistics for the dashboard
$stats = [];

// Total patients today
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) as count FROM patients WHERE lab_id = ? AND DATE(created_at) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $lab_id, $today);
$stmt->execute();
$stats['patients_today'] = $stmt->get_result()->fetch_assoc()['count'];

// Total exams today
$sql = "SELECT COUNT(*) as count FROM patient_exams pe 
        JOIN patients p ON pe.patient_id = p.id 
        WHERE p.lab_id = ? AND DATE(pe.date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $lab_id, $today);
$stmt->execute();
$stats['exams_today'] = $stmt->get_result()->fetch_assoc()['count'];

// Revenue today
$sql = "SELECT SUM(e.price) as total FROM patient_exams pe 
        JOIN patients p ON pe.patient_id = p.id 
        JOIN exams e ON pe.exam_id = e.id 
        WHERE p.lab_id = ? AND DATE(pe.date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $lab_id, $today);
$stmt->execute();
$stats['revenue_today'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Pending results
$sql = "SELECT COUNT(*) as count FROM patient_exams pe 
        JOIN patients p ON pe.patient_id = p.id 
        LEFT JOIN results r ON pe.patient_id = r.patient_id AND pe.exam_id = r.exam_id 
        WHERE p.lab_id = ? AND r.id IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lab_id);
$stmt->execute();
$stats['pending_results'] = $stmt->get_result()->fetch_assoc()['count'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($stats);