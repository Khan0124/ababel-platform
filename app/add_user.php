
<?php
include 'auth.php';
include 'config.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = $_POST['full_name'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $email = $_POST['email'];
  $role = $_POST['role'];
  $status = $_POST['status'];
  $office = $_POST['office'];

  $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, email, role, status, office)
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssss", $full_name, $username, $password, $email, $role, $status, $office);

  if ($stmt->execute()) {
    header("Location: users.php");
    exit;
  } else {
    $error = "⚠️ فشل في إضافة الموظف. قد يكون اسم المستخدم مكرر.";
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة موظف</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; padding: 30px; }
    form { background: white; max-width: 500px; margin: auto; padding: 20px; border-radius: 10px; }
    h2 { text-align: center; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input, select { width: 100%; padding: 10px; margin-top: 5px; }
    button { width: 100%; margin-top: 20px; padding: 10px; background: #711739; color: white; border: none; border-radius: 5px; }
    .back-link { display: block; text-align: center; margin-top: 15px; color: #711739; font-weight: bold; }
    .error { color: red; text-align: center; margin-top: 10px; }
  </style>
</head>
<body>

<form method="POST">
  <h2>➕ إضافة موظف جديد</h2>

  <label>الاسم الكامل:</label>
  <input type="text" name="full_name" id="full_name" required oninput="generateUsername()">

  <label>اسم المستخدم:</label>
  <input type="text" name="username" id="username" required readonly>

  <label>كلمة المرور:</label>
  <input type="password" name="password" required>

  <label>البريد الإلكتروني:</label>
  <input type="email" name="email">

  <label>الدور:</label>
  <select name="role" required>
    <option value="مدير عام">مدير عام</option>
    <option value="محاسب">محاسب</option>
    <option value="مدير مكتب">مدير مكتب</option>
  </select>

  <label>الحالة:</label>
  <select name="status" required>
    <option value="مفعل">مفعل</option>
    <option value="غير مفعل">غير مفعل</option>
  </select>

  <label>المكتب:</label>
  <select name="office" required>
    <option value="بورتسودان">بورتسودان</option>
    <option value="عطبرة">عطبرة</option>
    <option value="الصين">الصين</option>
  </select>

  <button type="submit">حفظ الموظف</button>
  <a class="back-link" href="users.php">⬅ العودة لإدارة الموظفين</a>

  <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
  <?php endif; ?>
</form>

<script>
function generateUsername() {
  const fullName = document.getElementById('full_name').value.trim();
  if (!fullName) return;
  const parts = fullName.split(' ');
  let username = '';
  if (parts.length >= 2) {
    username = parts[0].charAt(0).toLowerCase() + parts[1].toLowerCase();
  } else {
    username = parts[0].toLowerCase();
  }
  document.getElementById('username').value = username;
}
</script>

</body>
</html>
