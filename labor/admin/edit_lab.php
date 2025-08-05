<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$id = $_GET['id'];
$lab = $conn->query("SELECT * FROM labs WHERE id = $id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_main = $_POST['phone_main'];
    $phone_secondary = $_POST['phone_secondary'];
    $address = $_POST['address'];
    $map_link = $_POST['map_link'];

    // معالجة اللوقو الجديد
    $logo = $lab['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = uniqid('logo_') . '.' . $ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], "../assets/$logo");
    }

    $stmt = $conn->prepare("UPDATE labs SET name=?, email=?, phone_main=?, phone_secondary=?, address=?, map_link=?, logo=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $email, $phone_main, $phone_secondary, $address, $map_link, $logo, $id);
    $stmt->execute();
    header("Location: labs_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل معمل</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-4">تعديل بيانات المعمل</h3>
  <form method="post" enctype="multipart/form-data">
    <div class="row mb-3">
      <div class="col"><input name="name" class="form-control" value="<?= $lab['name'] ?>" required></div>
      <div class="col"><input name="email" type="email" class="form-control" value="<?= $lab['email'] ?>" required></div>
    </div>
    <div class="row mb-3">
      <div class="col"><input name="phone_main" class="form-control" value="<?= $lab['phone_main'] ?>" required></div>
      <div class="col"><input name="phone_secondary" class="form-control" value="<?= $lab['phone_secondary'] ?>"></div>
    </div>
    <div class="mb-3"><input name="address" class="form-control" value="<?= $lab['address'] ?>"></div>
    <div class="mb-3"><input name="map_link" class="form-control" value="<?= $lab['map_link'] ?>"></div>

    <div class="mb-3">
      <label for="logo">تغيير اللوقو (اختياري):</label>
      <input type="file" name="logo" id="logo" class="form-control">
      <?php if ($lab['logo']): ?>
        <img src="../assets/<?= $lab['logo'] ?>" width="80" class="mt-2">
      <?php endif; ?>
    </div>

    <button class="btn btn-success">💾 حفظ</button>
    <a href="labs_list.php" class="btn btn-secondary">رجوع</a>
  </form>
</div>
</body>
</html>
