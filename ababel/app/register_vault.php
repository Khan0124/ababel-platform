<?php
include 'config.php';
include 'auth.php';

// Calculate balance
$sum_in = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'قبض' AND source = 'سجل'")->fetch_assoc()['total'] ?? 0;
$sum_out = $conn->query("SELECT SUM(amount) as total FROM cashbox WHERE type = 'صرف' AND source = 'سجل'")->fetch_assoc()['total'] ?? 0;
$balance = $sum_in - $sum_out;

// Filtering
$type = $_GET['type'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = "cb.source = 'سجل'";

if (isset($_GET['type']) && in_array($type, ['قبض', 'صرف'])) {
    $where .= " AND cb.type = '$type'";
}

if ($from && $to) {
    $where .= " AND DATE(cb.created_at) BETWEEN '$from' AND '$to'";
}

// Fetch transactions
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
    <title>خزنة السجل</title>
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
            font-family: 'Cairo', sans-serif;
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
        }
        .stat-box {
            border-radius: 15px;
            padding: 25px 20px;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #344955 0%, #4a6572 100%);
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
        .table thead th {
            background-color: var(--table-header);
            color: white;
            font-weight: 600;
            padding: 15px;
            border: none;
        }
        .row-in td { background-color: rgba(209, 231, 221, 0.3); border-left: 3px solid var(--secondary-color); }
        .row-out td { background-color: rgba(255, 243, 205, 0.3); border-left: 3px solid #ffc107; }
        .amount-in { color: var(--secondary-color); font-weight: 700; }
        .amount-out { color: #dc3545; font-weight: 700; }
        .badge-type {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .badge-in { background-color: rgba(25, 135, 84, 0.15); color: var(--secondary-color); }
        .badge-out { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0"><i class="fas fa-book me-3"></i> خزنة السجل</h1>
                <p class="mb-0 opacity-75">إدارة عمليات القبض والصرف المتعلقة بالسجل</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="cashbox.php" class="btn btn-light btn-lg">
                    <i class="fas fa-home me-2"></i>العودة للخزنة الرئيسية
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="stat-box">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                        <h5>رصيد خزنة السجل</h5>
                        <h2><?= number_format($balance) ?> جنيه</h2>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-line fa-3x opacity-25"></i>
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
                <div class="col-md-3">
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-dark w-100" title="بحث"><i class="fas fa-search"></i></button>
                </div>
            </form>
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
                            <td colspan="7">
                                <div class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <h5>لا توجد عمليات لعرضها</h5>
                                    <p class="mt-2">استخدم أدوات الفلترة أعلاه أو أضف عمليات جديدة عبر يوميات الصرف</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>