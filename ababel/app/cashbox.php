<?php
include 'config.php';
include 'auth.php';

// Calculate balances for each vault
$vaults = [
    'سجل' => ['name' => 'خزنة السجل', 'url' => 'register_vault.php'],
    'تختيم' => ['name' => 'خزنة التختيم', 'url' => 'stamping_vault.php'],
    'موانئ' => ['name' => 'خزنة الموانئ', 'url' => 'ports_vault.php'],
    'منفستو' => ['name' => 'خزنة المنفستو', 'url' => 'manifesto_vault.php'], // تم تغيير المفتاح إلى 'منفستو'
];

$vault_balances = [];
$total_balance = 0;

foreach ($vaults as $source => $info) {
    $sum_in = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'قبض' AND source = '$source'")->fetch_assoc()['total'] ?? 0;
    $sum_out = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'صرف' AND source = '$source'")->fetch_assoc()['total'] ?? 0;
    $vault_balances[$source] = $sum_in - $sum_out;
    $total_balance += $vault_balances[$source];
}

// Insurance balance (unchanged)
$insurance_in = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'قبض' AND source = 'رصيد التأمين'")->fetch_assoc()['total'] ?? 0;
$insurance_refund = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'صرف' AND source = 'استرداد التأمين'")->fetch_assoc()['total'] ?? 0;
$insurance_transfer = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'قبض' AND source = 'تحويل من التأمين'")->fetch_assoc()['total'] ?? 0;
$insurance_balance = $insurance_in - $insurance_refund - $insurance_transfer;

// Filtering (unchanged)
$type = $_GET['type'] ?? '';
$source = $_GET['source'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = "1";

if (isset($_GET['type']) && in_array($type, ['قبض', 'صرف'])) {
    $where .= " AND cb.type = '$type'";
}

if (!empty($source)) {
    $where .= " AND cb.source = '" . mysqli_real_escape_string($conn, $source) . "'";
}

if ($from && $to) {
    $where .= " AND DATE(cb.created_at) BETWEEN '$from' AND '$to'";
}

// Fetch transactions (unchanged)
$res = $conn->query("
    SELECT cb.*, u.full_name AS user_name
    FROM cashbox cb
    LEFT JOIN users u ON cb.user_id = u.id
    WHERE $where
    ORDER BY cb.id DESC
    LIMIT 100
");

if (!$res) {
    echo "<div style='color:red;text-align:center;'>خطأ في الاستعلام: " . $conn->error . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نظام الخزنة - الإدارة المالية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #711739;
            --secondary-color: #198754;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
            --success-light: #d1e7dd;
            --warning-light: #fff3cd;
            --table-header: #4a6572;
        }
        
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            padding: 20px;
            color: #333;
        }
        
        .page-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, #8a2e4d 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            background-size: cover;
            background-position: center;
        }
        
        .stat-box {
            border-radius: 15px;
            padding: 25px 20px;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-box::after {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
        }
        
        .bg-balance { 
            background: linear-gradient(135deg, var(--primary-color) 0%, #8a2e4d 100%); 
        }
        
        .bg-insurance { 
            background: linear-gradient(135deg, var(--secondary-color) 0%, #1da65d 100%); 
        }
        
        .bg-vault { 
            background: linear-gradient(135deg, #344955 0%, #4a6572 100%); 
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .filter-form input, 
        .filter-form select {
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
            height: 45px;
        }
        
        .filter-form input:focus, 
        .filter-form select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(113, 23, 57, 0.15);
        }
        
        .btn-custom {
            border-radius: 10px;
            padding: 10px 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-dark {
            background: linear-gradient(135deg, #4a6572 0%, #344955 100%);
            border: none;
        }
        
        .btn-dark:hover {
            background: linear-gradient(135deg, #344955 0%, #232f34 100%);
            transform: translateY(-2px);
        }
        
        .btn-outline-custom {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .action-buttons .btn {
            margin-left: 8px;
            margin-bottom: 8px;
            border-radius: 10px;
            min-width: 180px;
            text-align: right;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            padding: 0;
            margin-bottom: 30px;
        }
        
        .table-title {
            background: linear-gradient(90deg, #4a6572 0%, #344955 100%);
            color: white;
            padding: 15px 25px;
            margin: 0;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        
        .table-responsive {
            border-radius: 0 0 15px 15px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: var(--table-header);
            color: white;
            font-weight: 600;
            padding: 15px;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(113, 23, 57, 0.05);
            transform: scale(1.005);
        }
        
        .table td {
            padding: 12px 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .row-in td {
            background-color: rgba(209, 231, 221, 0.3);
            border-left: 3px solid var(--secondary-color);
        }
        
        .row-out td {
            background-color: rgba(255, 243, 205, 0.3);
            border-left: 3px solid #ffc107;
        }
        
        .amount-in {
            color: var(--secondary-color);
            font-weight: 700;
        }
        
        .amount-out {
            color: #dc3545;
            font-weight: 700;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .no-data i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
            color: #adb5bd;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
            border-top: 1px solid #eaeaea;
            margin-top: 20px;
        }
        
        .badge-type {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .badge-in {
            background-color: rgba(25, 135, 84, 0.15);
            color: var(--secondary-color);
        }
        
        .badge-out {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .action-buttons .btn {
                min-width: 100%;
                margin-left: 0;
            }
            
            .stat-box {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0"><i class="fas fa-cash-register me-3"></i> إدارة الخزنة</h1>
                <p class="mb-0 opacity-75">إدارة التدفقات النقدية والعمليات المالية</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light btn-lg">
                    <i class="fas fa-home me-2"></i>العودة للرئيسية
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($vaults as $source => $info): ?>
            <div class="col-lg-3 col-md-6">
                <div class="stat-box bg-vault">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                            <h5><?= $info['name'] ?></h5>
                            <h2><a href="<?= $info['url'] ?>" style="color:white;text-decoration:none;"><?= number_format($vault_balances[$source]) ?> جنيه</a></h2>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-lg-3 col-md-6">
            <div class="stat-box bg-balance">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                        <h5>الرصيد الإجمالي</h5>
                        <h2><?= number_format($total_balance) ?> جنيه</h2>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-line fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-box bg-insurance">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                        <h5>رصيد التأمين</h5>
                        <h2><?= number_format($insurance_balance) ?> جنيه</h2>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-lock fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title text-primary mb-4"><i class="fas fa-filter me-2"></i>فلترة العمليات</h5>
            <form class="row g-3 filter-form" method="GET">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">كل الأنواع</option>
                        <option value="قبض" <?= $type === 'قبض' ? 'selected' : '' ?>>قبض</option>
                        <option value="صرف" <?= $type === 'صرف' ? 'selected' : '' ?>>صرف</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="source" class="form-select">
                        <option value="">كل المصادر</option>
                        <option <?= $source === 'رصيد التأمين' ? 'selected' : '' ?>>رصيد التأمين</option>
                        <option <?= $source === 'استرداد التأمين' ? 'selected' : '' ?>>استرداد التأمين</option>
                        <option <?= $source === 'تحويل من التأمين' ? 'selected' : '' ?>>تحويل من التأمين</option>
                        <option <?= $source === 'سجل' ? 'selected' : '' ?>>سجل</option>
                        <option <?= $source === 'تختيم' ? 'selected' : '' ?>>تختيم</option>
                        <option <?= $source === 'موانئ' ? 'selected' : '' ?>>موانئ</option>
                        <option <?= $source === 'منفستو' ? 'selected' : '' ?>>منفستو</option> <!-- تم تغيير الاسم هنا -->
                        <option <?= $source === 'مصروفات مكتب' ? 'selected' : '' ?>>مصروفات مكتب</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-dark w-100 btn-custom" title="بحث"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title text-primary mb-4"><i class="fas fa-plus-circle me-2"></i>إضافة عمليات جديدة</h5>
            <div class="action-buttons">
                <a href="office_expense.php" class="btn btn-outline-custom">
                    <i class="fas fa-building me-2"></i>إضافة مصروف مكتب
                </a>
                <a href="daily_income.php" class="btn btn-outline-custom">
                    <i class="fas fa-money-bill-wave me-2"></i>إضافة يومية قبض
                </a>
                <a href="daily_expense.php" class="btn btn-outline-custom">
                    <i class="fas fa-receipt me-2"></i>إضافة يومية صرف
                </a>
                <a href="register_vault.php" class="btn btn-outline-custom">
                    <i class="fas fa-book me-2"></i>إدارة خزنة السجل
                </a>
                <a href="stamping_vault.php" class="btn btn-outline-custom">
                    <i class="fas fa-stamp me-2"></i>إدارة خزنة التختيم
                </a>
                <a href="ports_vault.php" class="btn btn-outline-custom">
                    <i class="fas fa-anchor me-2"></i>إدارة خزنة الموانئ
                </a>
                <a href="manifesto_vault.php" class="btn btn-outline-custom">
                    <i class="fas fa-ship me-2"></i>إدارة خزنة المنفستو
                </a>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h5 class="table-title"><i class="fas fa-list me-2"></i>آخر العمليات</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>رقم العملية</th>
                        <th>النوع</th>
                        <th>المصدر</th>
                        <th>البيان</th>
                        <th>الطريقة</th>
                        <th>المبلغ</th>
                        <th>الموظف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res && $res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                            <tr class="<?= $row['type'] === 'قبض' ? 'row-in' : 'row-out' ?>">
                                <td><?= date("Y-m-d", strtotime($row['created_at'])) ?></td>
                                <td><?= date("Ymd", strtotime($row['created_at'])) . '-' . $row['id'] ?></td>
                                <td>
                                    <span class="badge-type <?= $row['type'] === 'قبض' ? 'badge-in' : 'badge-out' ?>">
                                        <i class="fas <?= $row['type'] === 'قبض' ? 'fa-arrow-down me-1' : 'fa-arrow-up me-1' ?>"></i>
                                        <?= $row['type'] ?>
                                    </span>
                                </td>
                                <td><?= $row['source'] ?? '-' ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['method'] ?? '-' ?></td>
                                <td class="<?= $row['type'] === 'قبض' ? 'amount-in' : 'amount-out' ?>">
                                    <i class="fas <?= $row['type'] === 'قبض' ? 'fa-plus-circle me-1' : 'fa-minus-circle me-1' ?>"></i>
                                    <?= number_format($row['amount']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-user me-1"></i>
                                        <?= $row['user_name'] ?? 'غير معروف' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <h5>لا توجد عمليات لعرضها</h5>
                                    <p class="mt-2">استخدم أدوات الفلترة أعلاه أو أضف عمليات جديدة</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p class="text-muted">آخر تحديث: <?= date('Y-m-d H:i') ?></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>