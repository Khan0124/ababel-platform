<?php
session_start();
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$stock_id = $_GET['id'] ?? 0;

// جلب اسم المادة
$stmt = $conn->prepare("SELECT name FROM stock_items WHERE id = ? AND lab_id = ?");
$stmt->bind_param("ii", $stock_id, $lab_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("⚠️ المادة غير موجودة أو لا تتبع هذا المعمل.");
}

// استلام قيم الفلاتر من GET مع تعيين قيم افتراضية
$filter_type = $_GET['type'] ?? '';
$filter_employee = $_GET['employee'] ?? '';
$filter_from_date = $_GET['from_date'] ?? '';
$filter_to_date = $_GET['to_date'] ?? '';

// جلب بيانات الموظفين لقائمة الفلترة
$employees_result = $conn->query("SELECT id, name FROM lab_employees WHERE lab_id = $lab_id ORDER BY name ASC");

// بناء شروط الفلترة بشكل ديناميكي
$where = "sm.lab_id = ?";
$params = [$lab_id];
$types = "i";

// شرط المادة (stock_id)
$where .= " AND (sm.stock_id = ? OR sm.stock_id IS NULL OR sm.stock_id = 0)";
$params[] = $stock_id;
$types .= "i";

// فلتر نوع الحركة
if ($filter_type !== '') {
    if ($filter_type === 'خصم نتيجة فحص') {
        $where .= " AND (sm.reason LIKE ? OR sm.reason LIKE ?)";
        $params[] = '%نتيجة الفحص%';
        $params[] = '%نتيجة%';
        $types .= "ss";
    } else {
        $where .= " AND sm.movement_type = ?";
        $params[] = $filter_type;
        $types .= "s";
    }
}

// فلتر الموظف
if ($filter_employee !== '') {
    $where .= " AND sm.employee_id = ?";
    $params[] = $filter_employee;
    $types .= "i";
}

// فلتر التاريخ (من)
if ($filter_from_date !== '') {
    $where .= " AND sm.created_at >= ?";
    $params[] = $filter_from_date . " 00:00:00";
    $types .= "s";
}

// فلتر التاريخ (إلى)
if ($filter_to_date !== '') {
    $where .= " AND sm.created_at <= ?";
    $params[] = $filter_to_date . " 23:59:59";
    $types .= "s";
}

$sql = "SELECT sm.*, u.name AS employee_name FROM stock_movements sm
LEFT JOIN lab_employees u ON sm.employee_id = u.id
WHERE $where
ORDER BY sm.created_at DESC";

$history = $conn->prepare($sql);
$history->bind_param($types, ...$params);
$history->execute();
$result = $history->get_result();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>سجل حركة المخزون</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary">📜 سجل الحركات - <?= htmlspecialchars($item['name']) ?></h4>
    <a href="stock_list.php" class="btn btn-secondary">↩️ رجوع</a>
  </div>

  <!-- نموذج الفلاتر -->
  <form method="GET" class="row g-2 mb-4 align-items-end">
    <input type="hidden" name="id" value="<?= htmlspecialchars($stock_id) ?>" />
    <div class="col-auto">
      <label for="from_date" class="form-label">من تاريخ</label>
      <input type="date" id="from_date" name="from_date" class="form-control" value="<?= htmlspecialchars($filter_from_date) ?>" />
    </div>
    <div class="col-auto">
      <label for="to_date" class="form-label">إلى تاريخ</label>
      <input type="date" id="to_date" name="to_date" class="form-control" value="<?= htmlspecialchars($filter_to_date) ?>" />
    </div>
    <div class="col-auto">
      <label for="type" class="form-label">نوع الحركة</label>
      <select id="type" name="type" class="form-select">
        <option value="">الكل</option>
        <option value="إدخال" <?= $filter_type === 'إدخال' ? 'selected' : '' ?>>إدخال</option>
        <option value="إخراج" <?= $filter_type === 'إخراج' ? 'selected' : '' ?>>إخراج</option>
        <option value="خصم نتيجة فحص" <?= $filter_type === 'خصم نتيجة فحص' ? 'selected' : '' ?>>خصم نتيجة فحص</option>
      </select>
    </div>
    <div class="col-auto">
      <label for="employee" class="form-label">الموظف</label>
      <select id="employee" name="employee" class="form-select">
        <option value="">الكل</option>
        <?php while ($emp = $employees_result->fetch_assoc()): ?>
          <option value="<?= $emp['id'] ?>" <?= $filter_employee == $emp['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($emp['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">تصفية</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-hover text-center align-middle bg-white">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>النوع</th>
          <th>الكمية</th>
          <th>السبب</th>
          <th>الموظف</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
        <?php
          if ($row['movement_type'] === 'إدخال') {
              $label = 'إدخال';
              $color = 'success';
          } elseif (strpos($row['reason'], 'نتيجة الفحص') !== false || strpos($row['reason'], 'نتيجة') !== false) {
              $label = 'خصم نتيجة فحص';
              $color = 'warning';
          } else {
              $label = 'إخراج';
              $color = 'danger';
          }
        ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><span class="badge bg-<?= $color ?>"><?= $label ?></span></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= htmlspecialchars($row['reason']) ?></td>
          <td><?= htmlspecialchars($row['employee_name'] ?? '---') ?></td>
          <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
