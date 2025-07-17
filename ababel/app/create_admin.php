<?php
include 'config.php'; // الاتصال بقاعدة البيانات

// بيانات المستخدم
$username = 'admin';
$password = '123456'; // غيّرها حسب رغبتك
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin'; // أو أي نوع صلاحية تستخدمه في نظامك

$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $hashed_password, $role);

if ($stmt->execute()) {
    echo "✅ تم إنشاء المستخدم بنجاح<br>اسم المستخدم: $username<br>كلمة المرور: $password";
} else {
    echo "❌ خطأ أثناء إنشاء المستخدم: " . $conn->error;
}

$conn->close();
?>
