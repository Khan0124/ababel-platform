<?php
include '../includes/config.php';

$name = 'مدير التجربة';
$email = 'manager@lab.com';
$password = password_hash('123456', PASSWORD_DEFAULT);
$lab_id = 2;
$role = 'مدير';
$status = 'نشط';

$stmt = $conn->prepare("INSERT INTO lab_employees (lab_id, name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $lab_id, $name, $email, $password, $role, $status);

if ($stmt->execute()) {
    echo "تم إنشاء حساب المدير بنجاح ✅";
} else {
    echo "فشل: " . $stmt->error;
}
?>
