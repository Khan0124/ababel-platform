<?php
include 'auth.php';
include 'config.php';
include 'backup_notice_for_dashboard.php';
include 'check_permissions.php'; // ملف جديد للتحقق من الصلاحيات

// التحقق من صلاحيات المستخدم
if (!hasPermission('view_dashboard')) {
    header('Location: 403.php');
    exit();
}

$username = $_SESSION['username'] ?? 'الموظف';
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ar');
$_SESSION['lang'] = $lang;

// دالة مساعدة لتنسيق المبالغ المالية
function format_money($amount) {
    return number_format($amount) . ' جنيه';
}

// تحسين الاستعلامات باستخدام استعلام واحد
$stats_query = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM clients) AS client_count,
        (SELECT COUNT(*) FROM containers) AS container_count,
        (SELECT 
            (SELECT SUM(amount) FROM cashbox WHERE type = 'قبض' AND (source IS NULL OR source != 'رصيد التأمين')) -
            (SELECT SUM(amount) FROM cashbox WHERE type = 'صرف' AND (source IS NULL OR source != 'استرداد التأمين'))
        ) AS balance,
        (SELECT 
            (IFNULL((SELECT SUM(amount) FROM cashbox WHERE type = 'قبض' AND source = 'رصيد التأمين'), 0) -
            IFNULL((SELECT SUM(amount) FROM cashbox WHERE type = 'صرف' AND source = 'استرداد التأمين'), 0) -
            IFNULL((SELECT SUM(amount) FROM cashbox WHERE type = 'قبض' AND source = 'تحويل من التأمين'), 0))
        ) AS insurance_balance
");

$stats = $stats_query->fetch_assoc();
$client_count = $stats['client_count'] ?? 0;
$container_count = $stats['container_count'] ?? 0;
$balance = $stats['balance'] ?? 0;
$insurance_balance = $stats['insurance_balance'] ?? 0;

// الحاويات اقتربت من الأرضيات - استخدام prepared statement
$near_ground_stmt = $conn->prepare("
    SELECT c.id, c.container_number, cos.created_at
    FROM containers c
    JOIN (
        SELECT container_id, MAX(created_at) AS max_created_at
        FROM container_operational_status
        GROUP BY container_id
    ) latest ON c.id = latest.container_id
    JOIN container_operational_status cos 
        ON cos.container_id = latest.container_id 
        AND cos.created_at = latest.max_created_at
    WHERE cos.status = 'في الميناء' AND DATEDIFF(NOW(), cos.created_at) >= 18
");

$near_ground_stmt->execute();
$near_ground = $near_ground_stmt->get_result();

// الحاويات تجاوزت 30 يومًا - استخدام prepared statement
$overdue_containers_stmt = $conn->prepare("
    SELECT c.id, c.container_number, c.entry_date
    FROM containers c
    LEFT JOIN container_operational_status cos ON c.id = cos.container_id
    WHERE cos.id IS NULL AND DATEDIFF(NOW(), c.entry_date) >= 30
");

$overdue_containers_stmt->execute();
$overdue_containers = $overdue_containers_stmt->get_result();

// آخر العمليات
$recent_ops = $conn->query("
    SELECT cb.*, u.full_name 
    FROM cashbox cb
    LEFT JOIN users u ON cb.user_id = u.id
    ORDER BY cb.created_at DESC
    LIMIT 5
");

// وقت آخر تحديث للبيانات
$last_updated = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <title>لوحة تحكم أبابيل</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Cairo', sans-serif; background: #f9f9f9; }
    .navbar-custom {
      background-color: #711739;
      padding: 10px 20px;
    }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link,
    .navbar-custom .dropdown-toggle {
      color: white !important;
      font-weight: bold;
    }
    .navbar-custom .nav-link:hover {
      background-color: #8a1c47;
      border-radius: 6px;
    }
    .logo-img { height: 50px; margin-left: 10px; }
    .dropdown-menu { text-align: <?= $lang === 'ar' ? 'right' : 'left' ?>; }
    .welcome { margin-top: 50px; text-align: center; color: #711739; }
    .card-box { 
        background: white; 
        padding: 15px; 
        border-radius: 10px; 
        box-shadow: 0 0 5px #ccc;
        border-left: 4px solid #711739;
    }
    .card-box.primary { border-left-color: #711739; }
    .card-box.success { border-left-color: #28a745; }
    .card-box.danger { border-left-color: #dc3545; }
    .card-box.warning { border-left-color: #ffc107; }
    .dashboard-section { padding: 30px 20px; }
    .section-title { 
        color: #711739; 
        margin-bottom: 15px;
        border-bottom: 2px solid #711739;
        padding-bottom: 8px;
    }
    .last-updated {
        text-align: center;
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 20px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <img src="logo.png" alt="شعار" class="logo-img">
      <span>أبابيل</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLinks">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarLinks">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">الرئيسية</a></li>
        <li class="nav-item"><a class="nav-link" href="clients_list.php">العملاء</a></li>
        <li class="nav-item"><a class="nav-link" href="containers.php">الحاويات</a></li>
        <li class="nav-item"><a class="nav-link" href="cashbox.php">الخزنة</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">التقارير</a></li>
      </ul>
      <div class="dropdown">
        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">القائمة</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="commercial_operations.php"> <i class="bi bi-journal-bookmark"></i> العمليات التجارية</a></li>
          <li><a class="dropdown-item" href="gov_actions.php"> <i class="bi bi-building"></i> الإجراءات الحكومية</a></li>
          <li><a class="dropdown-item" href="registries.php"> <i class="bi bi-archive"></i> السجلات</a></li>
          <li><a class="dropdown-item" href="daily_expense_list.php"><i class="bi bi-receipt"></i> يوميات الصرف</a></li>
          <li><a class="dropdown-item" href="documents.php"><i class="bi bi-folder"></i> المستندات</a></li>
          <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> الإعدادات</a></li>
          <li><a class="dropdown-item" href="users.php"><i class="bi bi-people"></i> الموظفين</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="?lang=ar"><i class="bi bi-translate"></i> العربية</a></li>
          <li><a class="dropdown-item" href="?lang=en"><i class="bi bi-translate"></i> English</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container dashboard-section">
  <div class="welcome">
    <h2>مرحبًا بك يا <?= htmlspecialchars($username) ?> في لوحة تحكم شركة أبابيل</h2>
    <p>اختر من القائمة أعلاه للتنقل بين الأقسام.</p>
    <div class="last-updated">آخر تحديث: <?= $last_updated ?></div>
  </div>

  <div class="row mt-4 text-center">
    <div class="col-md-3 mb-3">
        <div class="card-box primary">
            <i class="bi bi-people fs-4"></i> 
            <strong>عدد العملاء:</strong> <?= $client_count ?>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card-box success">
            <i class="bi bi-box fs-4"></i> 
            <strong>عدد الحاويات:</strong> <?= $container_count ?>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card-box warning">
            <i class="bi bi-cash fs-4"></i> 
            <strong>الرصيد الحالي:</strong> <?= format_money($balance) ?>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card-box danger">
            <i class="bi bi-shield fs-4"></i> 
            <strong>رصيد التأمين:</strong> <?= format_money($insurance_balance) ?>
        </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-6 mb-4">
      <div class="card-box">
        <h5 class="section-title"><i class="bi bi-exclamation-triangle"></i> الحاويات التي اقتربت من الأرضيات</h5>
        <ul class="list-group">
          <?php if ($near_ground->num_rows > 0): while($c = $near_ground->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($c['container_number']) ?>
              <span class="badge bg-danger">قضت <?= (new DateTime())->diff(new DateTime($c['created_at']))->days ?> يوم</span>
            </li>
          <?php endwhile; else: ?>
            <li class="list-group-item">لا توجد حاويات قريبة من فترة الأرضيات.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="col-md-6 mb-4">
      <div class="card-box">
        <h5 class="section-title"><i class="bi bi-clock-history"></i> حاويات تجاوزت 30 يومًا</h5>
        <ul class="list-group">
          <?php if ($overdue_containers->num_rows > 0): while($c = $overdue_containers->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($c['container_number']) ?> (منذ <?= (new DateTime())->diff(new DateTime($c['entry_date']))->days ?> يوم)
              <a href="view_container.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">عرض</a>
            </li>
          <?php endwhile; else: ?>
            <li class="list-group-item">لا توجد حاويات تجاوزت 30 يومًا بدون حالة.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="col-md-12 mb-4">
      <div class="card-box">
        <h5 class="section-title"><i class="bi bi-receipt"></i> آخر العمليات المالية</h5>
        <ul class="list-group">
          <?php while($op = $recent_ops->fetch_assoc()): ?>
            <li class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <span class="fw-bold"><?= htmlspecialchars($op['description']) ?></span>
                  <span class="badge bg-<?= $op['type'] === 'قبض' ? 'success' : 'danger' ?> ms-2">
                    <?= htmlspecialchars($op['type']) ?>
                  </span>
                </div>
                <div>
                  <span class="text-primary"><?= format_money($op['amount']) ?></span>
                  <span class="text-muted ms-3"><?= date('Y-m-d', strtotime($op['created_at'])) ?></span>
                  <span class="text-secondary ms-3"><?= htmlspecialchars($op['full_name'] ?? '') ?></span>
                </div>
              </div>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- زر مزامنة عائم + حالة الاتصال -->
<button id="syncBtn" title="مزامنة" style="
  position: fixed;
  bottom: 20px;
  left: 20px;
  z-index: 9999;
  background-color: #0d6efd;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 30px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
">
  <i class="bi bi-arrow-repeat"></i> مزامنة
</button>

<!-- إشعار -->
<div id="syncStatus" style="
  position: fixed;
  bottom: 80px;
  left: 20px;
  z-index: 9999;
  display: none;
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffeeba;
  padding: 10px 15px;
  border-radius: 8px;
  font-size: 14px;
  max-width: 300px;
"></div>

<script>
const syncBtn = document.getElementById('syncBtn');
const syncStatus = document.getElementById('syncStatus');

// تحديد ملف المزامنة حسب البيئة (محلي أو سيرفر)
const isLocal = location.hostname === 'localhost';
const syncScript = isLocal ? 'sync_send.php' : 'sync_receive_all.php';

function syncData() {
  syncBtn.disabled = true;
  syncBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> جاري المزامنة...';
  syncStatus.innerHTML = '⏳ جاري المزامنة...';
  syncStatus.style.display = 'block';

  fetch(syncScript)
    .then(res => res.text())
    .then(data => {
      syncStatus.innerHTML = '✅ تمت المزامنة بنجاح!';
      syncBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> مزامنة';
      console.log('✅ رد:', data);
      setTimeout(() => {
          syncStatus.style.display = 'none';
          location.reload(); // إعادة تحميل الصفحة بعد المزامنة
      }, 3000);
    })
    .catch(err => {
      syncStatus.innerHTML = '❌ فشل الاتصال أو السيرفر غير متاح.';
      syncBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> مزامنة';
      console.error('❌ خطأ:', err);
      setTimeout(() => syncStatus.style.display = 'none', 5000);
      syncBtn.disabled = false;
    });
}

syncBtn.addEventListener('click', syncData);
</script>

<script>
// التحويل عند فقد الإنترنت
window.addEventListener('offline', () => {
  alert('❌ تم فقد الاتصال بالإنترنت. يتم التحويل إلى النظام المحلي...');
  window.location.href = 'http://localhost/ababel/app/dashboard.php';
});

// التحويل عند عودة الإنترنت (إذا كنت في النسخة المحلية فقط)
window.addEventListener('online', () => {
  if (location.hostname === 'localhost') {
    alert('✅ تم استعادة الاتصال. العودة إلى النظام الأونلاين...');
    window.location.href = 'https://ababel.net/app/dashboard.php';
  } else {
    syncStatus.innerHTML = '✅ تم استعادة الاتصال. تتم الآن المزامنة...';
    syncStatus.style.display = 'block';
    syncData();
  }
});
</script>

</body>
</html>