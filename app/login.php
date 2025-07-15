<?php
session_start();
include 'config.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $remember = isset($_POST['remember']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'مفعل'");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['office'] = $user['office'];

      if ($remember) {
        setcookie("remember_username", $username, time() + (86400 * 30), "/");
      } else {
        setcookie("remember_username", "", time() - 3600, "/");
      }

      $redirect_url = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
unset($_SESSION['redirect_after_login']); // تنظيف الجلسة بعد الاستخدام
header("Location: $redirect_url");
exit;

      exit;
    }
  }
  $error = "⚠️ اسم المستخدم أو كلمة المرور غير صحيحة.";
}

$saved_username = $_COOKIE['remember_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تسجيل الدخول - أبابيل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');

    body {
      font-family: 'Cairo', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(to bottom right, #711739, #5c1130);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .login-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      width: 100%;
      max-width: 370px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      z-index: 1;
      animation: floatUp 1s ease forwards;
    }

    @keyframes floatUp {
      from { transform: translateY(40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .login-box img {
      display: block;
      margin: 0 auto 20px;
      width: 80px;
      animation: fadeIn 1.4s ease-in-out;
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #711739;
    }

    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .remember-me {
      margin-top: 12px;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
    }

    button {
      width: 100%;
      margin-top: 20px;
      padding: 12px;
      background: #711739;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    .error {
      color: red;
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    /* خطوط متحركة */
    .animated-bg {
      position: absolute;
      width: 100%;
      height: 100%;
      background-image: linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0.05) 75%, transparent 75%, transparent);
      background-size: 40px 40px;
      animation: slide 10s linear infinite;
      z-index: 0;
    }

    @keyframes slide {
      from { background-position: 0 0; }
      to { background-position: 40px 40px; }
    }

    @media(max-width: 480px) {
      .login-box {
        padding: 25px 20px;
      }
    }
  </style>
</head>
<body>

<div class="animated-bg"></div>

<div class="login-box">
  <img src="logo.png" alt="شعار الشركة">
  <h2>تسجيل الدخول</h2>
  <form method="POST">
    <label>اسم المستخدم:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($saved_username) ?>" required>

    <label>كلمة المرور:</label>
    <input type="password" name="password" required>

    <div class="remember-me">
      <input type="checkbox" name="remember" id="remember" <?= $saved_username ? 'checked' : '' ?>>
      <label for="remember">تذكرني</label>
    </div>

    <button type="submit">دخول</button>

    <?php if ($error): ?>
      <p class="error"><?= $error ?></p>
    <?php endif; ?>
  </form>
</div>

</body>
</html>