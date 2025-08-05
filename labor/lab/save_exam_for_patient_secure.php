<?php
/**
 * Secure Save Exam for Patient
 * Handles exam invoice creation with proper validation and security
 */

include 'auth_check.php';
include '../includes/config.php';
require_once '../includes/validation.php';

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_exam_for_patient.php');
    exit;
}

validateCSRF($_POST['csrf_token'] ?? '');

// Validation rules
$rules = [
    'patient_id' => 'required|integer',
    'exam_ids' => 'required',
    'exam_date' => 'required|date',
    'referred_by' => 'max:255',
    'insurance_company_id' => 'integer',
    'discount' => 'numeric|min:0',
    'notes' => 'max:1000'
];

// Validate input
$validation = validate($_POST, $rules);

if (!$validation['valid']) {
    $_SESSION['errors'] = $validation['errors'];
    $_SESSION['old_input'] = $_POST;
    header('Location: add_exam_for_patient.php');
    exit;
}

$data = $validation['data'];
$lab_id = $_SESSION['lab_id'];
$employee_id = $_SESSION['employee_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Verify patient belongs to this lab
    $stmt = $conn->prepare("SELECT id FROM patients WHERE id = ? AND lab_id = ?");
    $stmt->bind_param("ii", $data['patient_id'], $lab_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('المريض غير موجود أو غير مصرح بالوصول إليه');
    }
    $stmt->close();
    
    // Calculate total amount
    $exam_ids = array_filter($data['exam_ids']);
    if (empty($exam_ids)) {
        throw new Exception('يجب اختيار فحص واحد على الأقل');
    }
    
    // Prepare placeholders for IN clause
    $placeholders = str_repeat('?,', count($exam_ids) - 1) . '?';
    $types = str_repeat('i', count($exam_ids));
    
    // Get exam prices
    $sql = "SELECT id, price FROM exam_catalog WHERE id IN ($placeholders) AND lab_id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $params = array_merge($exam_ids, [$lab_id]);
    $stmt->bind_param($types . 'i', ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exam_prices = [];
    $total_amount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $exam_prices[$row['id']] = $row['price'];
        $total_amount += $row['price'];
    }
    $stmt->close();
    
    // Verify all exams were found
    if (count($exam_prices) !== count($exam_ids)) {
        throw new Exception('بعض الفحوصات المحددة غير صحيحة');
    }
    
    // Apply discount
    $discount = floatval($data['discount'] ?? 0);
    $final_amount = $total_amount - $discount;
    
    if ($final_amount < 0) {
        throw new Exception('الخصم أكبر من المبلغ الإجمالي');
    }
    
    // Create invoice
    $stmt = $conn->prepare("
        INSERT INTO exam_invoices 
        (lab_id, patient_id, invoice_date, total_amount, discount, 
         insurance_company_id, referred_by, notes, created_by, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $insurance_id = !empty($data['insurance_company_id']) ? $data['insurance_company_id'] : null;
    $referred_by = !empty($data['referred_by']) ? $data['referred_by'] : null;
    $notes = !empty($data['notes']) ? $data['notes'] : null;
    
    $stmt->bind_param(
        "iisdisssi",
        $lab_id,
        $data['patient_id'],
        $data['exam_date'],
        $total_amount,
        $discount,
        $insurance_id,
        $referred_by,
        $notes,
        $employee_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception('فشل في إنشاء الفاتورة');
    }
    
    $invoice_id = $stmt->insert_id;
    $stmt->close();
    
    // Add exams to patient_exams table
    $stmt = $conn->prepare("
        INSERT INTO patient_exams 
        (lab_id, patient_id, exam_id, invoice_id, exam_date, status, created_by, employee_id) 
        VALUES (?, ?, ?, ?, ?, 'قيد الإجراء', ?, ?)
    ");
    
    foreach ($exam_ids as $index => $exam_id) {
        if (empty($exam_id)) continue;
        
        $stmt->bind_param(
            "iiissii",
            $lab_id,
            $data['patient_id'],
            $exam_id,
            $invoice_id,
            $data['exam_date'],
            $employee_id,
            $employee_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception('فشل في إضافة الفحص');
        }
    }
    $stmt->close();
    
    // Add cashbox entry
    $stmt = $conn->prepare("
        INSERT INTO cashbox 
        (lab_id, transaction_date, type, description, source, amount, employee_id, method, notes) 
        VALUES (?, NOW(), 'قبض', ?, 'فحوصات', ?, ?, 'كاش', ?)
    ");
    
    $description = "تحصيل فحص للمريض ID:{$data['patient_id']} (فاتورة $invoice_id)";
    $cashbox_notes = "فاتورة فحوص رقم $invoice_id";
    
    $stmt->bind_param(
        "isdis",
        $lab_id,
        $description,
        $final_amount,
        $employee_id,
        $cashbox_notes
    );
    
    if (!$stmt->execute()) {
        throw new Exception('فشل في تسجيل المعاملة المالية');
    }
    $stmt->close();
    
    // Log activity
    $security->logSecurityEvent(
        'invoice_created',
        "Invoice #$invoice_id created for patient #{$data['patient_id']} - Amount: $final_amount",
        $_SERVER['REMOTE_ADDR'] ?? ''
    );
    
    // Commit transaction
    $conn->commit();
    
    // Success message
    $_SESSION['success'] = "تم إنشاء الفاتورة بنجاح - رقم الفاتورة: $invoice_id";
    
    // Redirect to invoice view
    header("Location: view_invoice.php?id=$invoice_id");
    exit;
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Log error
    $security->logSecurityEvent(
        'invoice_creation_failed',
        $e->getMessage(),
        $_SERVER['REMOTE_ADDR'] ?? ''
    );
    
    // Set error message
    $_SESSION['error'] = 'خطأ: ' . $e->getMessage();
    $_SESSION['old_input'] = $_POST;
    
    // Redirect back
    header('Location: add_exam_for_patient.php');
    exit;
}
?>