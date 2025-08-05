<?php
include 'auth_check.php';
include '../includes/config.php';

$lab_id = $_SESSION['lab_id'];

// استعلام واحد لجميع الإحصائيات بدلاً من استعلامات متعددة
$sql = "SELECT 
    (SELECT COUNT(*) FROM patients WHERE lab_id = ?) AS total_patients,
    (SELECT COUNT(*) FROM exam_catalog WHERE lab_id = ?) AS total_exams,
    (SELECT COUNT(*) FROM lab_employees WHERE lab_id = ?) AS total_employees,
    (SELECT COUNT(*) FROM patient_exams WHERE lab_id = ?) AS total_results,
    (SELECT COUNT(*) FROM cashbox WHERE lab_id = ?) AS total_transactions,
    (SELECT COUNT(*) FROM patient_exams WHERE lab_id = ? AND status != 'تم التسليم') AS unsubmitted_count";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $lab_id, $lab_id, $lab_id, $lab_id, $lab_id, $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

$total_patients = $stats['total_patients'];
$total_exams = $stats['total_exams'];
$total_employees = $stats['total_employees'];
$total_results = $stats['total_results'];
$total_transactions = $stats['total_transactions'];
$unsubmitted_count = $stats['unsubmitted_count'];

// معلومات المعمل
$stmt_lab = $conn->prepare("SELECT name, logo FROM labs WHERE id = ?");
$stmt_lab->bind_param("i", $lab_id);
$stmt_lab->execute();
$lab_result = $stmt_lab->get_result();
$lab = $lab_result->fetch_assoc();
$stmt_lab->close();
$lab_logo = !empty($lab['logo']) ? "../assets/" . htmlspecialchars($lab['logo']) : "../assets/default_logo.png";

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>لوحة تحكم المعمل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Tahoma', sans-serif;
      background-color: #f1f3f5;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      right: 0;
      top: 0;
      background-color: #212529;
      color: white;
      padding: 20px;
      overflow-y: auto;
    }
    .sidebar img {
      display: block;
      margin: auto;
      max-height: 70px;
      background-color: #fff;
      padding: 5px;
      border-radius: 10px;
      object-fit: contain;
    }
    .sidebar h5 {
      margin-top: 10px;
      text-align: center;
      font-size: 16px;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      padding: 10px;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      margin-bottom: 5px;
      font-weight: 600;
      font-size: 14px;
    }
    .sidebar a i {
      margin-left: 8px;
      font-size: 18px;
      width: 24px;
      text-align: center;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background-color: #343a40;
    }
    .main-content {
      margin-right: 260px;
      padding: 30px;
    }
    .card-box {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      height: 140px;
      transition: transform 0.3s, box-shadow 0.3s;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .card-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background-color: var(--card-color);
    }
    .card-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .card-box h6 {
      margin-bottom: 0;
      color: #6c757d;
      font-weight: 600;
      font-size: 0.95rem;
    }
    .card-box h4 {
      font-size: 1.5rem;
      margin: 0;
    }
    .card-box i {
      font-size: 36px;
      margin-bottom: 8px;
    }
    .alert-unsubmitted {
      background-color: #fff3cd;
      border: 1px solid #ffeeba;
      color: #856404;
      border-radius: 8px;
      padding: 12px 20px;
      margin-bottom: 25px;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
    }
    .alert-unsubmitted i {
      margin-left: 10px;
      font-size: 1.2rem;
    }
    .card-loader {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 4px;
      background-color: #e9ecef;
      overflow: hidden;
    }
    .card-loader::before {
      content: '';
      position: absolute;
      height: 100%;
      width: 100%;
      background-color: var(--card-color);
      animation: loading 1.5s infinite ease-in-out;
    }
    @keyframes loading {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    .update-time {
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 5px;
    }

    @media (max-width: 991px) {
      .card-box {
        height: 160px;
      }
    }
    
    @media (max-width: 767px) {
      .main-content {
        margin-right: 0;
        padding: 15px;
      }
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding: 15px;
      }
      .sidebar-links {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
      }
      .sidebar a {
        flex: 1 1 150px;
        font-size: 13px;
        padding: 8px 5px;
        justify-content: center;
      }
      .sidebar img {
        max-height: 60px;
      }
    }
    
    @media (max-width: 480px) {
      .sidebar a {
        flex-basis: 130px;
        font-size: 12px;
      }
      .card-box {
        height: 140px;
        padding: 15px;
      }
      .card-box h4 {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>

<!-- ✅ الشريط الجانبي -->
<div class="sidebar">
  <img src="<?= $lab_logo ?>" alt="شعار المعمل" />
  <h5><?= htmlspecialchars($lab['name']) ?></h5>
  <hr class="text-white" />
  <div class="sidebar-links">
    <a href="lab_dashboard.php" class="<?= $current_page == 'lab_dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
    <a href="patients_list.php" class="<?= $current_page == 'patients_list.php' ? 'active' : '' ?>"><i class="bi bi-person-vcard"></i> المرضى</a>
    <a href="add_exam_for_patient.php" class="<?= $current_page == 'add_exam_for_patient.php' ? 'active' : '' ?>"><i class="bi bi-plus-circle"></i> إضافة فحص لمريض</a>
    <a href="exam_list.php" class="<?= $current_page == 'exam_list.php' ? 'active' : '' ?>"><i class="bi bi-file-medical"></i> التحاليل</a>
    <a href="exams_list.php" class="<?= $current_page == 'exams_list.php' ? 'active' : '' ?>"><i class="bi bi-clipboard-data"></i> الفحوصات</a>
    <a href="results_list.php" class="<?= $current_page == 'results_list.php' ? 'active' : '' ?>"><i class="bi bi-journal-check"></i> النتائج</a>
    <a href="cashbox.php" class="<?= $current_page == 'cashbox.php' ? 'active' : '' ?>"><i class="bi bi-cash-coin"></i> المعاملات</a>
    <a href="stock_list.php" class="<?= $current_page == 'stock_list.php' ? 'active' : '' ?>"><i class="bi bi-box-seam"></i> المخزن</a>
    <a href="insurance_companies.php" class="<?= $current_page == 'insurance_companies.php' ? 'active' : '' ?>"><i class="bi bi-shield-check"></i> شركات التأمين</a>
    <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>"><i class="bi bi-graph-up-arrow"></i> التقارير</a>
    <?php if ($_SESSION['employee_role'] === 'مدير'): ?>
      <a href="employees_list.php" class="<?= $current_page == 'employees_list.php' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> الموظفون</a>
      <a href="shift_list.php" class="<?= $current_page == 'shift_list.php' ? 'active' : '' ?>"><i class="bi bi-calendar-range"></i> الشفتات</a>
    <?php endif; ?>
    <a href="lab_logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
  </div>
</div>

<!-- ✅ المحتوى الرئيسي -->
<div class="main-content">

  <?php if ($unsubmitted_count > 0): ?>
    <div class="alert-unsubmitted">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>يوجد <a href="results_list.php?filter=pending" class="fw-bold text-decoration-none"><?= $unsubmitted_count ?> نتيجة فحص</a> لم تُسلم بعد</span>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h5>👋 مرحباً، <?= htmlspecialchars($_SESSION['employee_name']) ?> (<?= htmlspecialchars($_SESSION['employee_role']) ?>)</h5>
    <small class="update-time">آخر تحديث: <?= date('H:i - Y/m/d') ?></small>
  </div>

  <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3">
    <div class="col">
      <div class="card-box" onclick="window.location.href='patients_list.php'" style="--card-color: #0d6efd;">
        <i class="bi bi-person-lines-fill text-primary"></i>
        <h6>عدد المرضى</h6>
        <h4><?= $total_patients ?></h4>
        <div class="card-loader"></div>
      </div>
    </div>
    <div class="col">
      <div class="card-box" onclick="window.location.href='exam_list.php'" style="--card-color: #198754;">
        <i class="bi bi-clipboard-pulse text-success"></i>
        <h6>عدد التحاليل</h6>
        <h4><?= $total_exams ?></h4>
        <div class="card-loader"></div>
      </div>
    </div>
    <div class="col">
      <div class="card-box" onclick="window.location.href='results_list.php'" style="--card-color: #ffc107;">
        <i class="bi bi-journal-text text-warning"></i>
        <h6>عدد النتائج</h6>
        <h4><?= $total_results ?></h4>
        <div class="card-loader"></div>
      </div>
    </div>
    <div class="col">
      <div class="card-box" onclick="window.location.href='employees_list.php'" style="--card-color: #0dcaf0;">
        <i class="bi bi-person-badge text-info"></i>
        <h6>عدد الموظفين</h6>
        <h4><?= $total_employees ?></h4>
        <div class="card-loader"></div>
      </div>
    </div>
    <div class="col">
      <div class="card-box" onclick="window.location.href='cashbox.php'" style="--card-color: #dc3545;">
        <i class="bi bi-currency-exchange text-danger"></i>
        <h6>عدد المعاملات</h6>
        <h4><?= $total_transactions ?></h4>
        <div class="card-loader"></div>
      </div>
    </div>
    <div class="col">
      <div class="card-box" style="--card-color: #198754;">
        <i class="bi bi-check-circle text-success"></i>
        <h6>الوضع الحالي</h6>
        <span class="badge bg-success fs-6">نشط</span>
        <div class="card-loader"></div>
      </div>
    </div>
  </div>
</div>

<script>
// تحديث تلقائي للإحصائيات كل 5 دقائق
setInterval(() => {
  fetch('get_stats.php?lab_id=<?= $lab_id ?>')
    .then(response => response.json())
    .then(data => {
      document.querySelectorAll('.card-box h4')[0].innerText = data.total_patients;
      document.querySelectorAll('.card-box h4')[1].innerText = data.total_exams;
      document.querySelectorAll('.card-box h4')[2].innerText = data.total_results;
      document.querySelectorAll('.card-box h4')[3].innerText = data.total_employees;
      document.querySelectorAll('.card-box h4')[4].innerText = data.total_transactions;
      
      if(data.unsubmitted_count > 0) {
        const alertElement = document.querySelector('.alert-unsubmitted');
        if(alertElement) {
          alertElement.querySelector('a').innerHTML = `${data.unsubmitted_count} نتيجة فحص`;
        } else {
          const newAlert = document.createElement('div');
          newAlert.className = 'alert-unsubmitted';
          newAlert.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>يوجد <a href="results_list.php?filter=pending" class="fw-bold text-decoration-none">${data.unsubmitted_count} نتيجة فحص</a> لم تُسلم بعد</span>
          `;
          document.querySelector('.main-content').prepend(newAlert);
        }
      } else {
        const alertElement = document.querySelector('.alert-unsubmitted');
        if(alertElement) alertElement.remove();
      }
      
      // تحديث وقت التحديث
      const now = new Date();
      document.querySelector('.update-time').textContent = 
        `آخر تحديث: ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')} - ${now.getFullYear()}/${(now.getMonth()+1).toString().padStart(2, '0')}/${now.getDate().toString().padStart(2, '0')}`;
    });
}, 300000); // 5 دقائق
</script>

</body>
</html>