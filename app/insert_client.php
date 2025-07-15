<?php
include 'config.php';
include 'auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = trim($_POST['code'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($code && $name && $phone && $password) {
    $check = $conn->prepare("SELECT id FROM clients WHERE code = ?");
    $check->bind_param("s", $code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $message = "⚠️ رقم العميل موجود مسبقًا.";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO clients (code, name, phone, password) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $code, $name, $phone, $hashed_password);
      if ($stmt->execute()) {
        $message = "✅ تم حفظ العميل بنجاح.";
      } else {
        $message = "❌ فشل في الحفظ: " . $conn->error;
      }
    }
  } else {
    $message = "⚠️ جميع الحقول مطلوبة.";
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة عميل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Cairo', sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    .container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 0 8px #ccc;
    }
    h2 {
      text-align: center;
      color: #711739;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      margin-top: 20px;
      width: 100%;
      background: #711739;
      color: white;
      border: none;
      padding: 12px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    .message {
      text-align: center;
      color: #333;
      margin-top: 15px;
      font-weight: bold;
    }
    a.back-link {
      display: inline-block;
      margin-top: 15px;
      color: #711739;
      text-decoration: none;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>➕ إضافة عميل جديد</h2>
  <form method="POST">
    <label>الاسم:</label>
    <input type="text" name="name" required>

    <label>الكود:</label>
    <input type="text" name="code" required>

    <label>رقم الهاتف:</label>
    <input type="text" name="phone" required>

    <label>كلمة المرور:</label>
    <input type="password" name="password" required>

    <button type="submit">💾 حفظ</button>
  </form>
  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>
  <div class="text-center">
    <a href="clients_list.php" class="back-link">👈 الرجوع لقائمة العملاء</a>
  </div>
</div>

</body>
</html>