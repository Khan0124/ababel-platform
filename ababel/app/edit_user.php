
<?php
include 'auth.php';
include 'config.php';

$id = intval($_GET['id']);
$error = "";

$res = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
if ($res->num_rows == 0) {
  die("🚫 الموظف غير موجود.");
}
$user = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = $_POST['full_name'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $role = $_POST['role'];
  $status = $_POST['status'];
  $office = $_POST['office'];

  $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, role=?, status=?, office=? WHERE id=?");
  $stmt->bind_param("ssssssi", $full_name, $username, $email, $role, $status, $office, $id);

  if ($stmt->execute()) {
    header("Location: users.php");
    exit;
  } else {
    $error = "⚠️ فشل التعديل.";
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل موظف</title>
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f2f2f2; padding: 30px; }
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
  <h2>✏️ تعديل بيانات الموظف</h2>

  <label>الاسم الكامل:</label>
  <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>

  <label>اسم المستخدم:</label>
  <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

  <label>البريد الإلكتروني:</label>
  <input type="email" name="email" value="<?php echo $user['email']; ?>">

  <label>الدور:</label>
  <select name="role" required>
    <?php
    $roles = ['مدير عام', 'محاسب', 'مدير مكتب'];
    foreach ($roles as $role) {
      $selected = $user['role'] === $role ? 'selected' : '';
      echo "<option value='$role' $selected>$role</option>";
    }
    ?>
  </select>

  <label>الحالة:</label>
  <select name="status" required>
    <option value="مفعل" <?php if ($user['status'] === 'مفعل') echo 'selected'; ?>>مفعل</option>
    <option value="غير مفعل" <?php if ($user['status'] === 'غير مفعل') echo 'selected'; ?>>غير مفعل</option>
  </select>

  <label>المكتب:</label>
  <select name="office" required>
    <?php
    $offices = ['بورتسودان', 'عطبرة', 'الصين'];
    foreach ($offices as $office) {
      $selected = $user['office'] === $office ? 'selected' : '';
      echo "<option value='$office' $selected>$office</option>";
    }
    ?>
  </select>

  <button type="submit">💾 حفظ التعديلات</button>
  <a class="back-link" href="users.php">⬅ العودة لإدارة الموظفين</a>

  <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
  <?php endif; ?>
</form>

</body>
</html>
