<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth_employee.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// قراءة الفلاتر من الريكوست
$filter_patient = $_GET['patient'] ?? '';
$filter_search_type = $_GET['search_type'] ?? 'name'; // القيمة الافتراضية: البحث بالاسم
$filter_status = $_GET['status'] ?? '';
$filter_from_date = $_GET['from_date'] ?? '';
$filter_to_date = $_GET['to_date'] ?? '';

// بناء شروط WHERE ديناميكيًا
$where = "pe.lab_id = $lab_id";

if ($filter_patient !== '') {
    $filter_patient_esc = $conn->real_escape_string($filter_patient);
    if ($filter_search_type === 'code') {
        // البحث بكود المريض
        $where .= " AND p.code LIKE '%$filter_patient_esc%'";
    } else {
        // البحث بالاسم (افتراضي)
        $where .= " AND p.name LIKE '%$filter_patient_esc%'";
    }
}

if ($filter_status !== '') {
    $filter_status_esc = $conn->real_escape_string($filter_status);
    $where .= " AND pe.status = '$filter_status_esc'";
}

if ($filter_from_date !== '') {
    $filter_from_date_esc = $conn->real_escape_string($filter_from_date);
    $where .= " AND DATE(pe.created_at) >= '$filter_from_date_esc'";
}

if ($filter_to_date !== '') {
    $filter_to_date_esc = $conn->real_escape_string($filter_to_date);
    $where .= " AND DATE(pe.created_at) <= '$filter_to_date_esc'";
}

// جلب النتائج حسب الفلاتر
$sql = "
  SELECT pe.*, p.name AS patient_name, p.phone, p.code AS patient_code, e.name_en AS exam_name, e.unit
  FROM patient_exams pe
  JOIN patients p ON pe.patient_id = p.id
  JOIN exam_catalog e ON pe.exam_id = e.id
  WHERE $where
  ORDER BY pe.created_at DESC
";

$results = $conn->query($sql);

// لجلب حالات الفحص الفريدة (لخيارات الفلتر)
$status_options_result = $conn->query("SELECT DISTINCT status FROM patient_exams WHERE lab_id = $lab_id");
$status_options = [];
while($row = $status_options_result->fetch_assoc()) {
    $status_options[] = $row['status'];
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>قائمة النتائج</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css' rel='stylesheet' />
  <script>
  function sendWhatsApp(name, phone, examName, value) {
    if (!phone) {
      alert("لا يوجد رقم هاتف للمريض");
      return;
    }

    let cleanPhone = phone.replace(/[^0-9]/g, '').trim();

    if (cleanPhone.startsWith("0")) {
      cleanPhone = "249" + cleanPhone.slice(1);
    }

    if (cleanPhone.length < 11 || cleanPhone.length > 15) {
      alert("⚠️ رقم الهاتف غير صالح");
      return;
    }

    const msg = `مرحباً ${name}\nنتيجتك لفحص ${examName}: ${value}`;
    const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`;
    window.open(url, '_blank');
  }
  </script>
</head>

<body class="bg-light">
<div class="container py-4">

  <!-- زر العودة -->
  <a href="lab_dashboard.php" class="btn btn-secondary mb-3">⬅️ العودة للوحة التحكم</a>

  <h4 class="mb-4 text-primary">📋 قائمة نتائج الفحوصات</h4>

  <!-- نموذج الفلاتر -->
  <form method="GET" class="row g-3 mb-4 align-items-end">
    <div class="col-md-3">
      <label for="patient" class="form-label">بحث حسب</label>
      <input type="text" class="form-control" id="patient" name="patient" value="<?= htmlspecialchars($filter_patient) ?>" placeholder="أدخل اسم المريض أو كوده">
    </div>
    <div class="col-md-2">
      <label for="search_type" class="form-label">نوع البحث</label>
      <select class="form-select" id="search_type" name="search_type">
        <option value="name" <?= ($filter_search_type === 'name') ? 'selected' : '' ?>>اسم المريض</option>
        <option value="code" <?= ($filter_search_type === 'code') ? 'selected' : '' ?>>كود المريض</option>
      </select>
    </div>
    <div class="col-md-2">
      <label for="status" class="form-label">الحالة</label>
      <select class="form-select" id="status" name="status">
        <option value="">كل الحالات</option>
        <?php foreach ($status_options as $status): ?>
          <option value="<?= htmlspecialchars($status) ?>" <?= ($filter_status === $status) ? 'selected' : '' ?>>
            <?= htmlspecialchars($status) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label for="from_date" class="form-label">من تاريخ</label>
      <input type="date" class="form-control" id="from_date" name="from_date" value="<?= htmlspecialchars($filter_from_date) ?>">
    </div>
    <div class="col-md-2">
      <label for="to_date" class="form-label">إلى تاريخ</label>
      <input type="date" class="form-control" id="to_date" name="to_date" value="<?= htmlspecialchars($filter_to_date) ?>">
    </div>
    <div class="col-md-1 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">تصفية</button>
    </div>
  </form>

  <table class="table table-bordered table-striped table-hover">
    <thead class="table-dark text-center">
      <tr>
        <th>#</th>
        <th>كود المريض</th>
        <th>المريض</th>
        <th>الفحص</th>
        <th>النتيجة</th>
        <th>الوحدة</th>
        <th>التاريخ</th>
        <th>الحالة</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while($row = $results->fetch_assoc()): 
        $raw_phone = preg_replace('/[^0-9]/', '', $row['phone']);
        $wa_phone = (str_starts_with($raw_phone, '0')) ? '249' . substr($raw_phone, 1) : $raw_phone;
        $wa_link = "https://wa.me/" . $wa_phone . "?text=" . urlencode(
          "مرحباً {$row['patient_name']}\nيمكنك تحميل نتيجة الفحص من الرابط التالي:\n" .
          "https://lab.scooly.net/lab/print_result_whatsapp.php?exam_id={$row['id']}"
        );
      ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['patient_code'] ?? 'غير متوفر') ?></td>
          <td><?= htmlspecialchars($row['patient_name'] ?? 'غير متوفر') ?></td>
          <td><?= htmlspecialchars($row['exam_name'] ?? 'غير متوفر') ?></td>
          <td><?= htmlspecialchars($row['value'] ?? 'غير متوفر') ?></td>
          <td><?= htmlspecialchars($row['unit'] ?? 'غير متوفر') ?></td>
          <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
          <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status'] ?? 'غير متوفر') ?></span></td>
          <td>
            <a href='print_result.php?exam_id=<?= $row['id'] ?>' class='btn btn-sm btn-outline-dark' target='_blank'>🖨️ طباعة</a>
            <a href='enter_exam_result.php?exam_id=<?= $row['id'] ?>' class='btn btn-sm btn-outline-primary'>✏️ تعديل</a>
            <a href='<?= $wa_link ?>' target='_blank' class='btn btn-sm btn-success'>📎 واتساب PDF</a>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($results->num_rows === 0): ?>
        <tr>
          <td colspan="9">لا توجد نتائج مطابقة.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
