<?php
session_start();
include '../includes/auth_employee.php';
include '../includes/config.php';

// عرض الأخطاء أثناء التطوير فقط
ini_set('display_errors', 1);
error_reporting(E_ALL);

// تأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال البيانات
    $name = $_POST['name'] ?? '';
    $name_en = $_POST['name_en'] ?? '';
    $code_exam = $_POST['code_exam'] ?? '';
    $category = $_POST['category'] ?? null;
    $price = $_POST['price'] ?? 0;
    $unit = $_POST['unit'] ?? '';
    $ref_value = $_POST['ref_value'] ?? '';
    $sample_type = $_POST['sample_type'] ?? null;
    $delivery_time = $_POST['duration'] ?? null;
    $description = $_POST['description'] ?? '';
    $components = $_POST['components'] ?? [];
    $lab_id = $_SESSION['lab_id'];

    // تحقق من التكرار
    $check = $conn->prepare("SELECT id FROM exam_catalog WHERE code_exam = ? AND lab_id = ?");
    $check->bind_param("si", $code_exam, $lab_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // إعادة التوجيه مع رسالة خطأ
        header("Location: add_exam.php?error=duplicate");
        exit;
    }

    // الإدخال في قاعدة البيانات
    $stmt = $conn->prepare("
        INSERT INTO exam_catalog 
        (lab_id, name, name_en, code_exam, category, price, unit, normal_range, sample_type, delivery_time, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssssdssss",
        $lab_id, $name, $name_en, $code_exam, $category, $price, $unit,
        $ref_value, $sample_type, $delivery_time, $description
    );

    if ($stmt->execute()) {
        $exam_id = $conn->insert_id;

        foreach ($components as $comp) {
            $item_id = (int)$comp['item_id'];
            $qty = (int)$comp['quantity'];
            $is_optional = 0;

            $stmt2 = $conn->prepare("
                INSERT INTO exam_components 
                (exam_id, item_id, quantity_needed, is_optional) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt2->bind_param("iiii", $exam_id, $item_id, $qty, $is_optional);
            $stmt2->execute();

            $conn->query("UPDATE stock_items SET quantity = quantity - $qty WHERE id = $item_id AND lab_id = $lab_id");
        }

        header("Location: add_exam.php?success=1");
        exit;
    } else {
        header("Location: add_exam.php?error=save");
        exit;
    }
} else {
    http_response_code(403);
    echo "غير مسموح";
}
?>
