<?php
include 'auth_check.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// معالجة العمليات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_name'])) {
        $name = trim($_POST['add_name']);
        if ($name != "") {
            $stmt = $conn->prepare("INSERT INTO insurance_companies (lab_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $lab_id, $name);
            $stmt->execute();
        }
    }

    if (isset($_POST['toggle_id'])) {
        $id = intval($_POST['toggle_id']);
        $conn->query("UPDATE insurance_companies SET is_active = IF(is_active=1, 0, 1) WHERE id = $id AND lab_id = $lab_id");
    }

    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $conn->query("DELETE FROM insurance_companies WHERE id = $id AND lab_id = $lab_id");
    }

    header("Location: insurance_companies.php");
    exit();
}

// جلب الشركات
$companies = $conn->query("SELECT * FROM insurance_companies WHERE lab_id = $lab_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>شركات التأمين</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h4 class="mb-4">🏥 إدارة شركات التأمين</h4>

  <form method="post" class="row g-3 mb-4">
    <div class="col-md-8">
      <input type="text" name="add_name" class="form-control" placeholder="اسم الشركة الجديدة" required>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-primary">➕ إضافة شركة</button>
    </div>
  </form>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>اسم الشركة</th>
        <th>الحالة</th>
        <th>تاريخ الإضافة</th>
        <th>الخيارات</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while ($row = $companies->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <?= ($row['is_active'] ?? 1) ? '<span class="text-success">مفعّلة</span>' : '<span class="text-danger">معطّلة</span>' ?>
          </td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <form method="post" style="display:inline-block">
              <input type="hidden" name="toggle_id" value="<?= $row['id'] ?>">
              <button class="btn btn-sm btn-warning">تفعيل/تعطيل</button>
            </form>
            <form method="post" style="display:inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
              <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
              <button class="btn btn-sm btn-danger">🗑 حذف</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
