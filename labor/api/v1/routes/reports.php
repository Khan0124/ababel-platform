<?php
/**
 * Reports API Routes
 */

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';

// Generate various reports
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lab_id = $_SESSION['lab_id'] ?? null;
    
    if (!$lab_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $report_type = $_GET['type'] ?? 'summary';
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    header('Content-Type: application/json');
    
    switch ($report_type) {
        case 'summary':
            // Summary statistics
            $stats = [];
            
            // Total patients
            $sql = "SELECT COUNT(*) as total FROM patients WHERE lab_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lab_id);
            $stmt->execute();
            $stats['total_patients'] = $stmt->get_result()->fetch_assoc()['total'];
            
            // Total exams
            $sql = "SELECT COUNT(*) as total FROM exams WHERE lab_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lab_id);
            $stmt->execute();
            $stats['total_exams'] = $stmt->get_result()->fetch_assoc()['total'];
            
            // Results in date range
            $sql = "SELECT COUNT(*) as total FROM results r 
                    JOIN patients p ON r.patient_id = p.id 
                    WHERE p.lab_id = ? AND r.performed_at BETWEEN ? AND ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $lab_id, $start_date, $end_date);
            $stmt->execute();
            $stats['results_in_period'] = $stmt->get_result()->fetch_assoc()['total'];
            
            // Revenue in date range
            $sql = "SELECT SUM(e.price) as total FROM patient_exams pe 
                    JOIN patients p ON pe.patient_id = p.id 
                    JOIN exams e ON pe.exam_id = e.id 
                    WHERE p.lab_id = ? AND pe.date BETWEEN ? AND ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $lab_id, $start_date, $end_date);
            $stmt->execute();
            $stats['revenue_in_period'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            
            echo json_encode(['data' => $stats]);
            break;
            
        case 'financial':
            // Financial report
            $sql = "SELECT DATE(pe.date) as date, COUNT(*) as exam_count, SUM(e.price) as revenue 
                    FROM patient_exams pe 
                    JOIN patients p ON pe.patient_id = p.id 
                    JOIN exams e ON pe.exam_id = e.id 
                    WHERE p.lab_id = ? AND pe.date BETWEEN ? AND ? 
                    GROUP BY DATE(pe.date) 
                    ORDER BY date";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $lab_id, $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode(['data' => $data]);
            break;
            
        case 'popular_exams':
            // Most popular exams
            $sql = "SELECT e.name, e.id, COUNT(*) as count, SUM(e.price) as revenue 
                    FROM patient_exams pe 
                    JOIN patients p ON pe.patient_id = p.id 
                    JOIN exams e ON pe.exam_id = e.id 
                    WHERE p.lab_id = ? AND pe.date BETWEEN ? AND ? 
                    GROUP BY e.id 
                    ORDER BY count DESC 
                    LIMIT 20";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $lab_id, $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            echo json_encode(['data' => $data]);
            break;
            
        case 'patient_demographics':
            // Patient demographics
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
                        SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count,
                        AVG(YEAR(CURDATE()) - YEAR(dob)) as avg_age
                    FROM patients 
                    WHERE lab_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lab_id);
            $stmt->execute();
            $demographics = $stmt->get_result()->fetch_assoc();
            
            echo json_encode(['data' => $demographics]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid report type']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);