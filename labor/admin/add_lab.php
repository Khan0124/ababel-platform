<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$error = '';

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

    // التحقق من وجود البريد الإلكتروني مسبقًا
    $check_lab = $conn->prepare("SELECT id FROM labs WHERE email = ?");
    $check_lab->bind_param("s", $email);
    $check_lab->execute();
    $check_lab->store_result();

    $check_mgr = $conn->prepare("SELECT id FROM lab_employees WHERE email = ?");
    $check_mgr->bind_param("s", $manager_email);
    $check_mgr->execute();
    $check_mgr->store_result();

    if ($check_lab->num_rows > 0) {
        $error = "📧 بريد المعمل مستخدم مسبقًا.";
    } elseif ($check_mgr->num_rows > 0) {
        $error = "📧 بريد المدير مستخدم مسبقًا.";
    } else {
        // رفع اللوقو (اختياري)
        $logo_filename = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_filename = uniqid('logo_') . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../assets/' . $logo_filename);
        }

        $stmt = $conn->prepare("INSERT INTO labs 
            (name, email, phone_main, phone_secondary, address, map_link, logo, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssssss", $name, $email, $phone_main, $phone_secondary, $address, $map_link, $logo_filename);

        if ($stmt->execute()) {
            $lab_id = $conn->insert_id;
            $stmt2 = $conn->prepare("INSERT INTO lab_employees 
                (lab_id, name, phone, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?, 'مدير', 'نشط')");
            $stmt2->bind_param("issss", $lab_id, $manager_name, $manager_phone, $manager_email, $manager_password);
            $stmt2->execute();

            header("Location: labs_list.php");
            exit;
        } else {
            $error = "حدث خطأ أثناء الحفظ.";
        }
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
  <h3 class="mb-4">➕ إضافة معمل جديد</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="row mb-3">
      <div class="col"><input name="name" class="form-control" placeholder="اسم المعمل" required></div>
      <div class="col"><input name="email" type="email" class="form-control" placeholder="البريد الإلكتروني" required></div>
    </div>
    <div class="row mb-3">
      <div class="col"><input name="phone_main" class="form-control" placeholder="الهاتف الأساسي" required></div>
      <div class="col"><input name="phone_secondary" class="form-control" placeholder="الهاتف الثانوي (اختياري)"></div>
    </div>
    <div class="mb-3"><input name="address" class="form-control" placeholder="العنوان"></div>
    <div class="mb-3"><input name="map_link" class="form-control" placeholder="رابط الخريطة (اختياري)"></div>
    <div class="mb-3">
      <label class="form-label">📷 لوقو المعمل (اختياري)</label>
      <input type="file" name="logo" class="form-control">
    </div>

    <h5 class="mt-4">👤 بيانات مدير المعمل</h5>
    <div class="row mb-3">
      <div class="col"><input name="manager_name" class="form-control" placeholder="اسم المدير" required></div>
      <div class="col"><input name="manager_phone" class="form-control" placeholder="هاتف المدير" required></div>
    </div>
    <div class="row mb-3">
      <div class="col"><input name="manager_email" type="email" class="form-control" placeholder="إيميل المدير" required></div>
      <div class="col"><input name="manager_password" type="password" class="form-control" placeholder="كلمة المرور" required></div>
    </div>

    <button class="btn btn-primary">✅ إضافة</button>
    <a href="labs_list.php" class="btn btn-secondary">رجوع</a>
  </form>
</div>
</body>
</html>
