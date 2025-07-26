<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// استقبال الفلاتر
$search = $conn->real_escape_string($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$expiryFilter = $_GET['expiry'] ?? '';

// جلب البيانات
$where = "WHERE lab_id = $lab_id";
if ($search) {
    $where .= " AND name LIKE '%$search%'";
}
$stocks = $conn->query("SELECT * FROM stock_items $where ORDER BY name ASC");

// دالة الحالة
function getStockStatus($quantity, $min) {
    if ($quantity < $min) return ['ناقص', 'danger'];
    if ($quantity == $min) return ['على وشك النفاد', 'warning'];
    return ['كافي', 'success'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>المخزن - قائمة المواد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary">📦 إدارة المخزون</h4>
    <div>
      <a href="add_stock.php" class="btn btn-success">➕ إضافة مادة جديدة</a>
      <a href="lab_dashboard.php" class="btn btn-secondary">🏠 عودة للوحة التحكم</a>
    </div>
  </div>

  <!-- ✅ فورم الفلاتر والبحث -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="🔍 ابحث باسم المادة..." value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">📦 كل الحالات</option>
        <option value="danger" <?= $statusFilter == 'danger' ? 'selected' : '' ?>>ناقص</option>
        <option value="warning" <?= $statusFilter == 'warning' ? 'selected' : '' ?>>على وشك النفاد</option>
        <option value="success" <?= $statusFilter == 'success' ? 'selected' : '' ?>>كافي</option>
      </select>
    </div>

    <div class="col-md-3">
      <select name="expiry" class="form-select">
        <option value="">⏳ كل تواريخ الانتهاء</option>
        <option value="near" <?= $expiryFilter == 'near' ? 'selected' : '' ?>>ستنتهي خلال 30 يوم</option>
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">تصفية</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-hover text-center align-middle bg-white">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>المادة</th>
          <th>النوع</th>
          <th>الكمية</th>
          <th>الوحدة</th>
          <th>الحد الأدنى</th>
          <th>الحالة</th>
          <th>تاريخ الانتهاء</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i = 1;
        while($row = $stocks->fetch_assoc()): 
          list($statusText, $statusColor) = getStockStatus($row['quantity'], $row['min_quantity']);

          // ⛔️ تصفية حسب حالة المادة (الكمية)
          if ($statusFilter && $statusFilter !== $statusColor) continue;

          // ⛔️ تصفية حسب قرب انتهاء الصلاحية (30 يوم)
          if ($expiryFilter === 'near' && $row['expiry_date']) {
              $expiry_ts = strtotime($row['expiry_date']);
              $today = time();
              $daysDiff = ($expiry_ts - $today) / (60 * 60 * 24);
              if ($daysDiff > 30) continue;
          } elseif ($expiryFilter === 'near' && !$row['expiry_date']) {
              continue;
          }
        ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['type']) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= htmlspecialchars($row['unit']) ?></td>
          <td><?= $row['min_quantity'] ?></td>
          <td><span class="badge bg-<?= $statusColor ?>"><?= $statusText ?></span></td>
          <td><?= $row['expiry_date'] ? date('Y-m-d', strtotime($row['expiry_date'])) : '-' ?></td>
          <td>
            <a href="edit_stock.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ تعديل</a>
            <a href="stock_movement.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">🔄 حركة</a>
            <a href="stock_history.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-dark">📜 سجل</a>
            <a href="delete_stock.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">🗑️ حذف</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
