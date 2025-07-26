<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// استقبال قيم الفلاتر من GET مع فلترة أساسية
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';

$filter_employee_name = $_GET['employee_name'] ?? '';
$filter_patient_name = $_GET['patient_name'] ?? '';
$filter_exam_name = $_GET['exam_name'] ?? '';

// لتحضير جمل WHERE في SQL
$where_date = '';
$where_date_cashbox = '';
$where_employees = "WHERE e.lab_id = $lab_id";
$where_patients = "WHERE p.lab_id = $lab_id";
$where_exams = "WHERE ec.lab_id = $lab_id";

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

if (validateDate($filter_start_date) && validateDate($filter_end_date)) {
    $where_date = " AND pe.exam_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
    $where_date_cashbox = " AND date BETWEEN '$filter_start_date' AND '$filter_end_date'";
}

if ($filter_employee_name !== '') {
    $filter_employee_name_escaped = $conn->real_escape_string($filter_employee_name);
    $where_employees .= " AND e.name LIKE '%$filter_employee_name_escaped%'";
}

if ($filter_patient_name !== '') {
    $filter_patient_name_escaped = $conn->real_escape_string($filter_patient_name);
    $where_patients .= " AND p.name LIKE '%$filter_patient_name_escaped%'";
}

if ($filter_exam_name !== '') {
    $filter_exam_name_escaped = $conn->real_escape_string($filter_exam_name);
    $where_exams .= " AND ec.name_en LIKE '%$filter_exam_name_escaped%'";
}

// إجماليات
$total_employees = $conn->query("SELECT COUNT(*) FROM lab_employees WHERE lab_id = $lab_id")->fetch_row()[0];
$total_patients = $conn->query("SELECT COUNT(*) FROM patients WHERE lab_id = $lab_id")->fetch_row()[0];
$total_exams = $conn->query("SELECT COUNT(*) FROM patient_exams WHERE lab_id = $lab_id $where_date")->fetch_row()[0];
$total_income = $conn->query("SELECT SUM(amount) FROM cashbox WHERE lab_id = $lab_id AND type = 'قبض' $where_date_cashbox")->fetch_row()[0] ?? 0;
$total_expense = $conn->query("SELECT SUM(amount) FROM cashbox WHERE lab_id = $lab_id AND type = 'صرف' $where_date_cashbox")->fetch_row()[0] ?? 0;
$net_balance = $total_income - $total_expense;

// الموظفين + عدد الفحوصات مع الفلاتر
$employee_works = $conn->query("
  SELECT e.name, e.role, COUNT(pe.id) AS exam_count
  FROM lab_employees e
  LEFT JOIN patient_exams pe ON pe.employee_id = e.id $where_date
  $where_employees
  GROUP BY e.id
");

// المرضى الأكثر تكرارًا مع الفلاتر
$top_patients = $conn->query("
  SELECT p.name, COUNT(pe.id) AS exams_done
  FROM patients p
  JOIN patient_exams pe ON pe.patient_id = p.id $where_date
  $where_patients
  GROUP BY p.id
  ORDER BY exams_done DESC
  LIMIT 5
");

// الفحوصات الأكثر استخدامًا مع الفلاتر
$popular_exams = $conn->query("
  SELECT ec.name_en, COUNT(pe.id) AS used_count
  FROM exam_catalog ec
  JOIN patient_exams pe ON pe.exam_id = ec.id $where_date
  $where_exams
  GROUP BY ec.id
  ORDER BY used_count DESC
  LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>📊 التقارير الشاملة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
  <style>
    .card-box { min-height: 100px; }
    .section-title { margin-top: 40px; margin-bottom: 15px; border-bottom: 2px solid #ccc; padding-bottom: 5px; }
    .filter-form .form-control { max-width: 300px; display: inline-block; margin-left: 10px; }
    .filter-form label { margin-left: 10px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">

  <h3 class="text-primary mb-4">📊 التقارير العامة للمعمل</h3>

  <!-- فلتر التاريخ الشامل -->
  <form method="GET" class="filter-form mb-4">
    <label for="start_date">من تاريخ:</label>
    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($filter_start_date) ?>" class="form-control" />
    <label for="end_date">إلى تاريخ:</label>
    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($filter_end_date) ?>" class="form-control" />
    
    <button type="submit" class="btn btn-primary mx-3">تصفية حسب التاريخ</button>
    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">إلغاء التصفية</a>
  </form>

  <!-- ملخصات -->
  <div class="row text-center mb-4">
    <div class="col-md-3"><div class="card card-box shadow-sm p-3"><h5>👥 الموظفين</h5><p class="fs-4 text-primary"><?= $total_employees ?></p></div></div>
    <div class="col-md-3"><div class="card card-box shadow-sm p-3"><h5>🧑‍⚕️ المرضى</h5><p class="fs-4 text-success"><?= $total_patients ?></p></div></div>
    <div class="col-md-3"><div class="card card-box shadow-sm p-3"><h5>🧪 الفحوصات</h5><p class="fs-4 text-warning"><?= $total_exams ?></p></div></div>
    <div class="col-md-3"><div class="card card-box shadow-sm p-3"><h5>💰 الرصيد الصافي</h5><p class="fs-4 text-danger"><?= number_format($net_balance, 2) ?> ر.س</p></div></div>
  </div>

  <!-- الموظفين -->
  <h5 class="section-title">👷‍♂️ الموظفون وعدد الفحوصات المنفذة</h5>

  <form method="GET" class="filter-form mb-3">
    <input type="hidden" name="start_date" value="<?= htmlspecialchars($filter_start_date) ?>" />
    <input type="hidden" name="end_date" value="<?= htmlspecialchars($filter_end_date) ?>" />

    <label for="employee_name">فلتر اسم الموظف:</label>
    <input type="text" id="employee_name" name="employee_name" value="<?= htmlspecialchars($filter_employee_name) ?>" placeholder="بحث بالاسم" class="form-control" />
    <button type="submit" class="btn btn-primary mx-3">تصفية</button>
    <a href="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query(['start_date' => $filter_start_date, 'end_date' => $filter_end_date]) ?>" class="btn btn-secondary">إلغاء التصفية</a>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered text-center bg-white">
      <thead class="table-dark"><tr><th>#</th><th>الاسم</th><th>الدور</th><th>عدد الفحوصات</th></tr></thead>
      <tbody>
        <?php $i=1; while($row = $employee_works->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['role']) ?></td>
          <td><?= $row['exam_count'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- المرضى -->
  <h5 class="section-title">👩‍⚕️ أكثر المرضى تكرارًا للفحوصات</h5>

  <form method="GET" class="filter-form mb-3">
    <input type="hidden" name="start_date" value="<?= htmlspecialchars($filter_start_date) ?>" />
    <input type="hidden" name="end_date" value="<?= htmlspecialchars($filter_end_date) ?>" />

    <label for="patient_name">فلتر اسم المريض:</label>
    <input type="text" id="patient_name" name="patient_name" value="<?= htmlspecialchars($filter_patient_name) ?>" placeholder="بحث بالاسم" class="form-control" />
    <button type="submit" class="btn btn-primary mx-3">تصفية</button>
    <a href="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query(['start_date' => $filter_start_date, 'end_date' => $filter_end_date]) ?>" class="btn btn-secondary">إلغاء التصفية</a>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered text-center bg-white">
      <thead class="table-dark"><tr><th>#</th><th>اسم المريض</th><th>عدد الفحوصات</th></tr></thead>
      <tbody>
        <?php $i=1; while($row = $top_patients->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= $row['exams_done'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- الفحوصات -->
  <h5 class="section-title">📈 أكثر الفحوصات طلبًا</h5>

  <form method="GET" class="filter-form mb-3">
    <input type="hidden" name="start_date" value="<?= htmlspecialchars($filter_start_date) ?>" />
    <input type="hidden" name="end_date" value="<?= htmlspecialchars($filter_end_date) ?>" />

    <label for="exam_name">فلتر اسم الفحص:</label>
    <input type="text" id="exam_name" name="exam_name" value="<?= htmlspecialchars($filter_exam_name) ?>" placeholder="بحث بالاسم" class="form-control" />
    <button type="submit" class="btn btn-primary mx-3">تصفية</button>
    <a href="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query(['start_date' => $filter_start_date, 'end_date' => $filter_end_date]) ?>" class="btn btn-secondary">إلغاء التصفية</a>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered text-center bg-white">
      <thead class="table-dark"><tr><th>#</th><th>اسم الفحص</th><th>عدد الاستخدام</th></tr></thead>
      <tbody>
        <?php $i=1; while($row = $popular_exams->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['name_en']) ?></td>
          <td><?= $row['used_count'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- المالية -->
  <h5 class="section-title">💸 التقرير المالي</h5>
  <div class="row text-center">
    <div class="col-md-4"><div class="card p-3"><h6>إجمالي الإيرادات</h6><p class="fs-5 text-success"><?= number_format($total_income, 2) ?> ر.س</p></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>إجمالي المصروفات</h6><p class="fs-5 text-danger"><?= number_format($total_expense, 2) ?> ر.س</p></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>الرصيد النهائي</h6><p class="fs-5 text-primary"><?= number_format($net_balance, 2) ?> ر.س</p></div></div>
  </div>

  <a href="lab_dashboard.php" class="btn btn-secondary mt-4">↩️ العودة للوحة التحكم</a>
</div>
</body>
</html>
