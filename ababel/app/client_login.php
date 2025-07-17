<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = $_POST['login'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM clients WHERE code = ? OR phone = ?");
  $stmt->bind_param("ss", $login, $login);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $client = $result->fetch_assoc();
    if (password_verify($password, $client['password'])) {
      $_SESSION['client_id'] = $client['id'];
      header("Location: client_dashboard.php");
      exit;
    } else {
      $error = "كلمة المرور غير صحيحة.";
    }
  } else {
    $error = "رمز العميل أو رقم الهاتف غير صحيح.";
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تسجيل دخول العميل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #711739, #a0284d);
      font-family: 'Cairo', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow-x: hidden;
    }
    .login-box {
      background-color: #fff;
      padding: 30px 25px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      width: 100%;
      max-width: 420px;
      opacity: 0;
      transform: translateY(40px);
      animation: slideIn 0.7s ease-out forwards;
    }
    .logo {
      display: block;
      margin: 0 auto 20px;
      height: 60px;
    }
    h4 {
      color: #711739;
      text-align: center;
      margin-bottom: 20px;
    }
    .btn-primary {
      background-color: #711739;
      border: none;
    }
    .btn-primary:hover {
      background-color: #8c1d44;
    }
    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

<div class="login-box">
  <img src="logo.png" alt="Logo" class="logo">
  <h4>تسجيل دخول العميل</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">رمز العميل أو رقم الهاتف</label>
      <input type="text" name="login" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">كلمة المرور</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">دخول</button>
  </form>
</div>

</body>
</html>