<?php
session_start();
include '../includes/config.php';
include '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_main = $_POST['phone_main'];
    $phone_secondary = $_POST['phone_secondary'];
    $address = $_POST['address'];
    $map_link = $_POST['map_link'];
    $manager_name = $_POST['manager_name'];
    $manager_phone = $_POST['manager_phone'];
    $manager_email = $_POST['manager_email'];
    $manager_password = password_hash($_POST['manager_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO labs (name, email, phone_main, phone_secondary, address, map_link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone_main, $phone_secondary, $address, $map_link);
    if ($stmt->execute()) {
        $lab_id = $conn->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO lab_managers (lab_id, name, phone, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issss", $lab_id, $manager_name, $manager_phone, $manager_email, $manager_password);
        $stmt2->execute();
        header("Location: labs_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة معمل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3 class="mb-4">إضافة معمل جديد</h3>
    <form method="post">
        <div class="row mb-3">
            <div class="col"><input name="name" class="form-control" placeholder="اسم المعمل" required></div>
            <div class="col"><input name="email" type="email" class="form-control" placeholder="البريد الإلكتروني" required></div>
        </div>
        <div class="row mb-3">
            <div class="col"><input name="phone_main" class="form-control" placeholder="رقم الهاتف الأساسي" required></div>
            <div class="col"><input name="phone_secondary" class="form-control" placeholder="رقم الهاتف الثانوي (اختياري)"></div>
        </div>
        <div class="mb-3"><input name="address" class="form-control" placeholder="عنوان المعمل"></div>
        <div class="mb-4"><input name="map_link" class="form-control" placeholder="رابط الموقع على الخريطة (اختياري)"></div>

        <h5>بيانات مدير المعمل</h5>
        <div class="row mb-3">
            <div class="col"><input name="manager_name" class="form-control" placeholder="اسم المدير" required></div>
            <div class="col"><input name="manager_phone" class="form-control" placeholder="رقم هاتف المدير" required></div>
        </div>
        <div class="row mb-3">
            <div class="col"><input name="manager_email" type="email" class="form-control" placeholder="إيميل المدير" required></div>
            <div class="col"><input name="manager_password" type="password" class="form-control" placeholder="كلمة مرور المدير" required></div>
        </div>
        <button type="submit" class="btn btn-primary">➕ إضافة</button>
        <a href="labs_list.php" class="btn btn-secondary">🔙 العودة للقائمة</a>
    </form>
</div>
</body>
</html>
