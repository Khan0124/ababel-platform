<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'] ?? null;
$employee_id = $_SESSION['employee_id'] ?? null;

if (!$lab_id || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['exam_id'])) {
    die("طلب غير صالح.");
}

$exam_id = (int) $_POST['exam_id'];
$value = trim($_POST['value']);
$comment = trim($_POST['comment'] ?? '');

// الحالة الافتراضية
$status = 'قيد الإجراء';
if ($value !== '') {
    $status = 'تم الاستخراج';
}

// تحديث النتيجة في قاعدة البيانات
$stmt = $conn->prepare("UPDATE patient_exams SET value = ?, comment = ?, status = ?, employee_id = ? WHERE id = ? AND lab_id = ?");
$stmt->bind_param("sssiii", $value, $comment, $status, $employee_id, $exam_id, $lab_id);
$stmt->execute();

// ✅ تنفيذ الخصم إذا الحالة "تم الاستخراج"
if ($status === 'تم الاستخراج') {
    // تأكد من عدم خصم سابق
    $reason = "خصم نتيجة للفحص رقم $exam_id";
    $check = $conn->prepare("SELECT COUNT(*) FROM stock_movements WHERE reason = ? AND lab_id = ?");
    $check->bind_param("si", $reason, $lab_id);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists == 0) {
        // جلب exam_id الحقيقي من جدول patient_exams
        $examQuery = $conn->prepare("SELECT exam_id FROM patient_exams WHERE id = ? AND lab_id = ?");
        $examQuery->bind_param("ii", $exam_id, $lab_id);
        $examQuery->execute();
        $examQuery->bind_result($real_exam_id);
        $examQuery->fetch();
        $examQuery->close();

        // جلب مكونات التحليل
        $compStmt = $conn->prepare("SELECT item_id, quantity_needed FROM exam_components WHERE exam_id = ?");
        $compStmt->bind_param("i", $real_exam_id);
        $compStmt->execute();
        $components = $compStmt->get_result();

        while ($row = $components->fetch_assoc()) {
            $item_id = $row['item_id'];
            $qty = $row['quantity_needed'];

            // خصم الكمية من المخزون
            $update = $conn->prepare("UPDATE stock_items SET quantity = quantity - ? WHERE id = ? AND lab_id = ?");
            $update->bind_param("iii", $qty, $item_id, $lab_id);
            $update->execute();

            // تسجيل الحركة
            $log = $conn->prepare("INSERT INTO stock_movements (item_id, lab_id, quantity, movement_type, reason) VALUES (?, ?, ?, 'subtract', ?)");
            $log->bind_param("iiis", $item_id, $lab_id, $qty, $reason);
            $log->execute();
        }
    }
}

header("Location: exams_list.php");
exit;
?>
