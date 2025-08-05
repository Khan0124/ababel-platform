<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/auth.php';
include '../includes/config.php';

$labs = $conn->query("SELECT * FROM labs ORDER BY created_at DESC");
if (!$labs) {
    die("خطأ في جلب البيانات: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة المعامل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">🧪 قائمة المعامل</h2>
        <a href="add_lab.php" class="btn btn-danger">➕ إضافة معمل جديد</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>اللوقو</th>
                    <th>الاسم</th>
                    <th>الإيميل</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $labs->fetch_assoc()): 
                    $logo = $row['logo'] ? "../assets/{$row['logo']}" : "../assets/default_lab.png";
                ?>
                <tr>
                    <td><img src="<?= $logo ?>" width="50" height="50" style="object-fit:contain;"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['phone_main'] ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= $row['status'] === 'active' ? 'نشط' : 'معطل' ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_lab.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
                        <a href="delete_lab.php?id=<?= $row['id'] ?>" onclick="return confirm('هل أنت متأكد؟')" class="btn btn-sm btn-danger">حذف</a>
                        <a href="toggle_lab.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">تفعيل/تعطيل</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
