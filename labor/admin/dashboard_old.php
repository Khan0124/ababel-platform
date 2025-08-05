<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';

$labs_count = $conn->query("SELECT COUNT(*) as total FROM labs")->fetch_assoc()['total'];
$tickets_open = $conn->query("SELECT COUNT(*) as total FROM tickets WHERE status = 'open'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>لوحة التحكم الرئيسية - نظام إدارة المختبرات الطبية</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/modern-style.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Tahoma', sans-serif;
    }

    .topbar {
      background: #ffffff;
      border-bottom: 1px solid #dee2e6;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar img {
      height: 40px;
      background: #fff;
      padding: 4px;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    .sidebar {
      background-color: #2c3e50;
      color: #fff;
      height: 100vh;
      padding-top: 20px;
      position: fixed;
      top: 58px;
      right: 0;
      width: 220px;
    }

    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #bdc3c7;
      text-decoration: none;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #34495e;
      color: #ffffff;
    }

    .main-content {
      margin-right: 220px;
      padding: 30px;
    }

    .card-stat {
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: 0.3s;
    }

    .card-stat:hover {
      transform: scale(1.02);
    }
  </style>
</head>
<body>

<!-- ✅ شريط علوي -->
<div class="topbar">
  <div class="d-flex align-items-center">
    <img src="/assets/logo.png" alt="Labor Logo">
    <strong class="ms-2 fs-5">Labor</strong>
  </div>
  <div class="text-muted">👋 مرحباً، <?= $_SESSION['admin_name'] ?? 'مشرف' ?></div>
</div>

<!-- ✅ القائمة الجانبية -->
<div class="sidebar">
  <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
  <a href="labs_list.php"><i class="bi bi-hospital"></i> إدارة المعامل</a>
  <a href="subscriptions_list.php"><i class="bi bi-briefcase"></i> الاشتراكات</a>
  <a href="tickets_list.php"><i class="bi bi-ticket-perforated"></i> التذاكر</a>
  <a href="activity_logs.php"><i class="bi bi-journal-text"></i> سجل الأنشطة</a>
  <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
</div>

<!-- ✅ محتوى الصفحة -->
<div class="main-content">
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card card-stat text-center bg-white p-4">
        <h5>عدد المعامل</h5>
        <h2><?= $labs_count ?></h2>
        <i class="bi bi-building text-primary fs-3"></i>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-stat text-center bg-white p-4">
        <h5>تذاكر مفتوحة</h5>
        <h2><?= $tickets_open ?></h2>
        <i class="bi bi-envelope-open text-danger fs-3"></i>
      </div>
    </div>
  </div>

  <div class="alert alert-info mt-4">
    ⏱ استخدم القائمة اليمنى للتنقل بين أقسام النظام المختلفة.
  </div>
</div>

</body>
</html>
