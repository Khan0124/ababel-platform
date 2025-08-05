<?php
session_start();
include '../includes/auth_employee.php';
include '../includes/config.php';

$exam_id = $_POST['exam_id'];
$lab_id = $_SESSION['lab_id'];

$name = $_POST['name'];
$name_en = $_POST['name_en'];
$code_exam = $_POST['code_exam'];
$price = $_POST['price'];
$unit = $_POST['unit'];
$ref_value = $_POST['ref_value'];
$description = $_POST['description'];

// تحديث بيانات التحليل
$stmt = $conn->prepare("UPDATE exam_catalog 
    SET name=?, name_en=?, code_exam=?, price=?, unit=?, normal_range=?, description=?
    WHERE id=? AND lab_id=?");
$stmt->bind_param("sssddssii", $name, $name_en, $code_exam, $price, $unit, $ref_value, $description, $exam_id, $lab_id);
$stmt->execute();

// تحديث المكونات القديمة
if (!empty($_POST['components_existing'])) {
    foreach ($_POST['components_existing'] as $component_id => $comp_name) {
        if (trim($comp_name) !== '') {
            $stmt = $conn->prepare("UPDATE exam_components SET name=? WHERE id=? AND exam_id=?");
            $stmt->bind_param("sii", $comp_name, $component_id, $exam_id);
            $stmt->execute();
        } else {
            // حذف المكون إذا كان الاسم فارغ
            $conn->query("DELETE FROM exam_components WHERE id=$component_id AND exam_id=$exam_id");
        }
    }
}

// إضافة مكونات جديدة
if (!empty($_POST['components_new'])) {
    foreach ($_POST['components_new'] as $comp_name) {
        $comp_name = trim($comp_name);
        if ($comp_name !== '') {
            $stmt = $conn->prepare("INSERT INTO exam_components (exam_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $exam_id, $comp_name);
            $stmt->execute();
        }
    }
}

header("Location: exam_list.php");
exit;
