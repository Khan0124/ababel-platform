<?php
include 'auth.php';
include 'config.php';

// الفلاتر
$type_filter = $_GET['type'] ?? '';
$date_filter = $_GET['date'] ?? '';
$where = "1";
if ($type_filter !== '') {
  $where .= " AND d.related_type = '" . mysqli_real_escape_string($conn, $type_filter) . "'";
}
if ($date_filter !== '') {
  $where .= " AND DATE(d.created_at) = '" . mysqli_real_escape_string($conn, $date_filter) . "'";
}

// استرجاع قائمة المستندات مع الفلاتر
$documents = $conn->query("SELECT d.*, u.full_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id WHERE $where ORDER BY d.created_at DESC");

$types = ['عام', 'قبض', 'صرف', 'حاوية', 'إجراء'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📁 المستندات - شركة أبابيل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f4f4f4; }
    .header { background: #711739; color: white; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; }
    .header img { height: 40px; }
    .container-box { background: white; border-radius: 10px; padding: 20px; margin-top: 20px; }
    label { font-weight: bold; }
  </style>
</head>
<body>
<div class="header">
  <div class="d-flex align-items-center">
    <img src="logo.png" alt="لوقو الشركة">
    <h4 class="ms-3">شركة أبابيل للتنمية والاستثمار المحدودة</h4>
  </div>
  <div>📁 قسم المستندات</div>
</div>
<div class="container container-box">
  <h5 class="mb-4">📤 رفع مستند جديد</h5>
  <form action="upload_document.php" method="post" enctype="multipart/form-data">
    <div class="row mb-3">
      <div class="col-md-6">
        <label>اسم المستند:</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>الملف:</label>
        <input type="file" name="document" class="form-control" required>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label>نوع المستند:</label>
        <select name="related_type" class="form-control" required>
          <?php foreach ($types as $type): ?>
            <option value="<?= $type ?>"><?= $type ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label>رقم الإيصال / الحاوية (اختياري):</label>
        <input type="text" name="related_id" class="form-control">
      </div>
    </div>
    <div class="mb-3">
      <label>ملاحظات:</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-success">رفع المستند</button>
    <a href="dashboard.php" class="btn btn-secondary ms-2">⬅️ العودة للوحة التحكم</a>
  </form>
</div>

<div class="container container-box mt-4">
  <h5 class="mb-4">📚 قائمة المستندات</h5>

  <!-- فلاتر -->
  <form method="get" class="row g-3 mb-3">
    <div class="col-md-4">
      <label>تصفية حسب النوع:</label>
      <select name="type" class="form-control">
        <option value="">-- الكل --</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= $t ?>" <?= $type_filter === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label>تصفية حسب التاريخ:</label>
      <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">تطبيق الفلاتر</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>الاسم</th>
          <th>النوع</th>
          <th>الرقم المرتبط</th>
          <th>ملاحظات</th>
          <th>الموظف</th>
          <th>الملف</th>
          <th>التاريخ</th>
          <th>الحذف</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($documents && $documents->num_rows > 0): $i = 1; while($doc = $documents->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($doc['title']) ?></td>
            <td><?= $doc['related_type'] ?></td>
            <td><?= $doc['related_id'] ?></td>
            <td><?= nl2br(htmlspecialchars($doc['description'])) ?></td>
            <td><?= $doc['full_name'] ?></td>
            <td><a href="uploads/<?= $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-primary">عرض</a></td>
            <td><?= date('Y-m-d', strtotime($doc['created_at'])) ?></td>
            <td><a href="delete_document.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستند؟')">🗑️ حذف</a></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="9">لا توجد مستندات مطابقة للفلترة.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
