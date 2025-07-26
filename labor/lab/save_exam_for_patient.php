<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth_employee.php';
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = $_SESSION['lab_id'];
    $employee_id = $_SESSION['employee_id'];

    $patient_id = intval($_POST['patient_id']);
    $exam_ids = $_POST['exam_ids'] ?? [];
    $referred_by = trim($_POST['referred_by'] ?? '');
    $exam_date = $_POST['exam_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    $discount = floatval($_POST['discount'] ?? 0);
    $insurance_company_id = !empty($_POST['insurance_company_id']) ? intval($_POST['insurance_company_id']) : null;

    if (!$patient_id || count($exam_ids) === 0) {
        die('❌ يرجى اختيار مريض وتحاليل.');
    }

    $exam_ids = array_map('intval', $exam_ids);
    $placeholders = implode(',', array_fill(0, count($exam_ids), '?'));

    $stmt = $conn->prepare("SELECT SUM(price) FROM exam_catalog WHERE id IN ($placeholders) AND lab_id = ?");
    $types = str_repeat('i', count($exam_ids)) . 'i';
    $stmt->bind_param($types, ...array_merge($exam_ids, [$lab_id]));
    $stmt->execute();
    $stmt->bind_result($total_amount);
    $stmt->fetch();
    $stmt->close();

    if ($total_amount === null) $total_amount = 0;
    if ($discount < 0) $discount = 0;
    if ($discount > $total_amount) $discount = $total_amount;
    $final_amount = $total_amount - $discount;

    if (!empty($referred_by)) {
        $check = $conn->prepare("SELECT id FROM referrals WHERE name = ? AND lab_id = ?");
        $check->bind_param("si", $referred_by, $lab_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $insertRef = $conn->prepare("INSERT INTO referrals (lab_id, name) VALUES (?, ?)");
            $insertRef->bind_param("is", $lab_id, $referred_by);
            $insertRef->execute();
            $insertRef->close();
        }
        $check->close();
    }

    $stmt = $conn->prepare("INSERT INTO exam_invoices 
        (lab_id, patient_id, invoice_date, total_amount, discount, referred_by, notes, insurance_company_id, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisddssii", 
        $lab_id, $patient_id, $exam_date, $total_amount, $discount, 
        $referred_by, $notes, $insurance_company_id, $employee_id);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO patient_exams (lab_id, patient_id, exam_id, invoice_id, exam_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($exam_ids as $exam_id) {
        $stmt->bind_param("iiiisi", $lab_id, $patient_id, $exam_id, $invoice_id, $exam_date, $employee_id);
        $stmt->execute();
    }
    $stmt->close();
    // خصم المكونات من المخزون وتسجيل الحركة
foreach ($exam_ids as $exam_id) {
    $components = $conn->query("SELECT item_id, quantity_needed FROM exam_components WHERE exam_id = $exam_id");
    while ($comp = $components->fetch_assoc()) {
        $item_id = $comp['item_id'];
        $qty = $comp['quantity_needed'];

        $conn->query("UPDATE stock_items SET quantity = quantity - $qty WHERE id = $item_id AND lab_id = $lab_id");

        $movement = $conn->prepare("INSERT INTO stock_movements 
            (lab_id, item_id, movement_type, quantity, reason, related_id, created_by) 
            VALUES (?, ?, 'خروج', ?, 'خصم تلقائي من فحص', ?, ?)");
        $movement->bind_param("iiiii", $lab_id, $item_id, $qty, $invoice_id, $employee_id);
        $movement->execute();
        $movement->close();
    }
}

$description = "تحصيل فحص للمريض ID:$patient_id (فاتورة $invoice_id)";
$now = date('Y-m-d H:i:s');
$source = 'فحوصات';
$method = 'كاش';
$cashbox_notes = "فاتورة فحوص رقم $invoice_id";

$stmt = $conn->prepare("INSERT INTO cashbox 
    (lab_id, transaction_date, type, description, source, method, notes, amount, created_by, created_at) 
    VALUES (?, ?, 'قبض', ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssdis", $lab_id, $now, $description, $source, $method, $cashbox_notes, $final_amount, $employee_id, $now);


    $stmt->execute();
    $stmt->close();

    header("Location: view_invoice.php?id=$invoice_id");
    exit();
} else {
    echo "❌ طريقة الطلب غير صحيحة";
}
