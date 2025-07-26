<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo "✅ تم إنشاء المشرف بنجاح.";
    } else {
        echo "❌ فشل في الإضافة: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء مشرف</title>
</head>
<body>
    <h2>إنشاء حساب مشرف جديد</h2>
    <form method="post">
        <input type="text" name="name" placeholder="اسم المشرف" required><br><br>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required><br><br>
        <input type="password" name="password" placeholder="كلمة المرور" required><br><br>
        <button type="submit">إنشاء</button>
    </form>
</body>
</html>
