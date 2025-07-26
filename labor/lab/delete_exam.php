<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$id = $_GET['id'];
$lab_id = $_SESSION['lab_id'];

// تأكد من أن التحليل يعود لهذا المعمل
$check = $conn->prepare("SELECT id FROM exam_catalog WHERE id = ? AND lab_id = ?");
$check->bind_param("ii", $id, $lab_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // حذف المكونات أولاً
    $conn->query("DELETE FROM exam_components WHERE exam_id = $id");
    // حذف التحليل
    $conn->query("DELETE FROM exam_catalog WHERE id = $id");
}

header("Location: exam_list.php");
exit;
