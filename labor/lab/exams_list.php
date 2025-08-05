<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'auth_check.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];
$employee_id = $_SESSION['employee_id'] ?? null;

$filter_employee_id = $_GET['employee_id'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_exam_id = $_GET['exam_id'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $exam_id = $_POST['exam_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE patient_exams SET status = ? WHERE id = ? AND lab_id = ?");
    $stmt->bind_param("sii", $new_status, $exam_id, $lab_id);
    $stmt->execute();

    if ($new_status === 'تم التسليم') {
        $stmt2 = $conn->prepare("SELECT e.price FROM patient_exams pe JOIN exam_catalog e ON pe.exam_id = e.id WHERE pe.id = ?");
        $stmt2->bind_param("i", $exam_id);
        $stmt2->execute();
        $stmt2->bind_result($amount);
        $stmt2->fetch();
        $stmt2->close();

        if ($amount > 0) {
            $stmt3 = $conn->prepare("INSERT INTO cashbox (lab_id, type, source, amount, employee_id) VALUES (?, 'قبض', 'فحص مختبر', ?, ?)");
            $stmt3->bind_param("idi", $lab_id, $amount, $employee_id);
            $stmt3->execute();
        }
    }

    $query_params = http_build_query($_GET);
    header("Location: exams_list.php?$query_params");
    exit;
}

$employees_result = $conn->query("SELECT id, name FROM lab_employees WHERE lab_id = $lab_id");
$exams_result = $conn->query("SELECT id, name FROM exam_catalog");

$status_colors = [
    'قيد الإجراء' => 'secondary',
    'تم الاستخراج' => 'info',
    'عمل استرداد' => 'warning',
    'تم التسليم' => 'success'
];

$where = "pe.lab_id = $lab_id";

if ($filter_employee_id !== '') {
    $filter_employee_id = (int)$filter_employee_id;
    $where .= " AND pe.employee_id = $filter_employee_id";
}

if ($filter_status !== '') {
    $filter_status_escaped = $conn->real_escape_string($filter_status);
    $where .= " AND pe.status = '$filter_status_escaped'";
}

if ($filter_exam_id !== '') {
    $filter_exam_id = (int)$filter_exam_id;
    $where .= " AND pe.exam_id = $filter_exam_id";
}

if ($filter_date_from !== '') {
    $date_from_escaped = $conn->real_escape_string($filter_date_from);
    $where .= " AND DATE(pe.created_at) >= '$date_from_escaped'";
}

if ($filter_date_to !== '') {
    $date_to_escaped = $conn->real_escape_string($filter_date_to);
    $where .= " AND DATE(pe.created_at) <= '$date_to_escaped'";
}

$results = $conn->query("
    SELECT pe.*, 
           p.name AS patient_name, p.gender, p.code AS patient_code, 
           e.name_en AS exam_name_en, e.price, 
           u.name AS employee_name 
    FROM patient_exams pe 
    JOIN patients p ON pe.patient_id = p.id 
    JOIN exam_catalog e ON pe.exam_id = e.id 
    LEFT JOIN lab_employees u ON pe.employee_id = u.id 
    WHERE $where 
    ORDER BY pe.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>قائمة الفحوصات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary">📋 قائمة الفحوصات</h4>
    <div>
      <a href="add_exam_for_patient.php" class="btn btn-success me-2">➕ إضافة فحص</a>
      <a href="lab_dashboard.php" class="btn btn-secondary">🔙 العودة للوحة التحكم</a>
    </div>
  </div>

  <!-- نموذج الفلترة -->
  <form method="GET" class="row g-3 mb-4 align-items-end">
    <div class="col-auto">
      <label for="employee_id" class="form-label">الموظف</label>
      <select id="employee_id" name="employee_id" class="form-select">
        <option value="">الكل</option>
        <?php while($emp = $employees_result->fetch_assoc()): ?>
          <option value="<?= $emp['id'] ?>" <?= $filter_employee_id == $emp['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($emp['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label for="status" class="form-label">الحالة</label>
      <select id="status" name="status" class="form-select">
        <option value="">الكل</option>
        <?php foreach ($status_colors as $s => $c): ?>
          <option value="<?= $s ?>" <?= $filter_status == $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label for="exam_id" class="form-label">نوع الفحص</label>
      <select id="exam_id" name="exam_id" class="form-select">
        <option value="">الكل</option>
        <?php
          $exams_result = $conn->query("SELECT id, name FROM exam_catalog");
          while($exam = $exams_result->fetch_assoc()):
        ?>
          <option value="<?= $exam['id'] ?>" <?= $filter_exam_id == $exam['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($exam['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label for="date_from" class="form-label">من تاريخ</label>
      <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>" />
    </div>
    <div class="col-auto">
      <label for="date_to" class="form-label">إلى تاريخ</label>
      <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>" />
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">تصفية</button>
    </div>
  </form>

  <table class="table table-bordered table-hover table-striped">
    <thead class="table-dark text-center">
      <tr>
        <th>#</th>
        <th>كود المريض</th>
        <th>اسم المريض</th>
        <th>الجنس</th>
        <th>الفحص (إنجليزي)</th>
        <th>السعر</th>
        <th>الحالة</th>
        <th>الموظف</th>
        <th>إدخال النتيجة</th>
        <th>تغيير الحالة</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while($row = $results->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['patient_code']) ?></td>
          <td><?= htmlspecialchars($row['patient_name']) ?></td>
          <td><?= $row['gender'] ?></td>
          <td><?= htmlspecialchars($row['exam_name_en']) ?></td>
          <td><?= number_format($row['price'], 2) ?> ج.س</td>
          <td>
            <span class="badge bg-<?= $status_colors[$row['status']] ?? 'secondary' ?>">
              <?= $row['status'] ?>
            </span>
          </td>
          <td><?= htmlspecialchars($row['employee_name'] ?? '-') ?></td>
          <td>
            <?php if (!in_array($row['status'], ['تم التسليم', 'تم الاستخراج'])): ?>
              <a href="enter_exam_result.php?exam_id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                إدخال النتيجة
              </a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <form method="POST" class="d-flex gap-2">
              <input type="hidden" name="exam_id" value="<?= $row['id'] ?>">
              <select name="status" class="form-select form-select-sm">
                <?php foreach ($status_colors as $s => $c): ?>
                  <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="update_status" class="btn btn-sm btn-primary">تحديث</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($results->num_rows === 0): ?>
        <tr><td colspan="10">لا توجد نتائج مطابقة للمعايير.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
