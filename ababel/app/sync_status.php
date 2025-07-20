<?php
include 'auth.php';
include 'config.php';

// Get sync statistics
$stats = [
    'total_synced' => 0,
    'last_7_days' => 0,
    'today' => 0
];

// Total synced containers
$result = $conn->query("SELECT COUNT(*) as count FROM containers WHERE china_loading_id IS NOT NULL");
if ($result) {
    $stats['total_synced'] = $result->fetch_assoc()['count'];
}

// Last 7 days
$result = $conn->query("SELECT COUNT(*) as count FROM containers WHERE china_loading_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $stats['last_7_days'] = $result->fetch_assoc()['count'];
}

// Today
$result = $conn->query("SELECT COUNT(*) as count FROM containers WHERE china_loading_id IS NOT NULL AND DATE(created_at) = CURDATE()");
if ($result) {
    $stats['today'] = $result->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حالة المزامنة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>حالة المزامنة مع نظام الصين</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>إجمالي الحاويات المزامنة</h5>
                        <h2><?= $stats['total_synced'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>آخر 7 أيام</h5>
                        <h2><?= $stats['last_7_days'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>اليوم</h5>
                        <h2><?= $stats['today'] ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="containers.php" class="btn btn-primary">العودة للحاويات</a>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>