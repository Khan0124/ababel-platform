<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth.php';
include 'config.php';

// Handle marking containers as seen
if (isset($_POST['mark_seen']) && $_SESSION['office'] === 'بورتسودان') {
    $containerId = intval($_POST['container_id']);
    $updateStmt = $conn->prepare("UPDATE containers SET seen_by_port = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $containerId);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit();
}

// Check for new containers from China
$newChinaContainers = 0;
if ($_SESSION['office'] === 'بورتسودان') {
    $newQuery = $conn->query("SELECT COUNT(*) as count FROM containers WHERE china_loading_id IS NOT NULL AND seen_by_port = 0");
    if ($newQuery) {
        $newCount = $newQuery->fetch_assoc();
        $newChinaContainers = intval($newCount['count']);
    }
}

// Get delayed containers
$delayed_containers = [];
$delayed_query = $conn->query("
    SELECT 
        c.id, 
        c.container_number, 
        c.client_name, 
        c.entry_date,
        c.bill_of_lading_date,
        c.bill_of_lading_status,
        c.tashitim_date,
        c.tashitim_status,
        COALESCE(c.bill_of_lading_date, bol_ops.date, bol_ops.created_at) AS actual_bill_date,
        COALESCE(c.tashitim_date, tash_ops.date, tash_ops.created_at) AS actual_tashitim_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Arrived') AS arrival_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Transported by Land') AS land_transport_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Delivered') AS delivery_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Empty Returned') AS empty_return_date
    FROM containers c
    LEFT JOIN container_operational_status bol_ops 
        ON c.id = bol_ops.container_id AND bol_ops.status = 'Bill of Lading Issued'
    LEFT JOIN container_operational_status tash_ops 
        ON c.id = tash_ops.container_id AND tash_ops.status = 'Customs Cleared'
    WHERE c.release_status = 'No'
");

if ($delayed_query && $delayed_query->num_rows > 0) {
    while ($row = $delayed_query->fetch_assoc()) {
        $delay_reasons = [];
        $days_overdue = 0;
        $current_date = new DateTime();

        // Check Bill of Lading delay
        if ($row['entry_date'] && $row['bill_of_lading_status'] !== 'issued' && !$row['actual_bill_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 10) {
                $delay_reasons[] = 'تأخير في إصدار البوليصة';
                $days_overdue = max($days_overdue, $days_since_entry - 10);
            }
        }

        // Check Tashitim delay
        if ($row['actual_bill_date'] && $row['tashitim_status'] !== 'done' && !$row['actual_tashitim_date']) {
            $bl_date = new DateTime($row['actual_bill_date']);
            $days_since_bl = $current_date->diff($bl_date)->days;
            if ($days_since_bl > 20) {
                $delay_reasons[] = 'تأخير في التختيم';
                $days_overdue = max($days_overdue, $days_since_bl - 20);
            }
        }

        // Check arrival delay
        if ($row['entry_date'] && !$row['arrival_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 60) {
                $delay_reasons[] = 'تأخير في الوصول';
                $days_overdue = max($days_overdue, $days_since_entry - 60);
            }
        }

        // Check land transport delay
        if ($row['arrival_date'] && !$row['land_transport_date']) {
            $arrival_date = new DateTime($row['arrival_date']);
            $days_since_arrival = $current_date->diff($arrival_date)->days;
            if ($days_since_arrival > 4) {
                $delay_reasons[] = 'تأخير في الشحن البري';
                $days_overdue = max($days_overdue, $days_since_arrival - 4);
            }
        }

        // Check delivery delay
        if ($row['arrival_date'] && !$row['delivery_date']) {
            $arrival_date = new DateTime($row['arrival_date']);
            $days_since_arrival = $current_date->diff($arrival_date)->days;
            if ($days_since_arrival > 7) {
                $delay_reasons[] = 'تأخير في التسليم';
                $days_overdue = max($days_overdue, $days_since_arrival - 7);
            }
        }

        // Check empty return delay
        if ($row['delivery_date'] && !$row['empty_return_date']) {
            $delivery_date = new DateTime($row['delivery_date']);
            $days_since_delivery = $current_date->diff($delivery_date)->days;
            if ($days_since_delivery > 7) {
                $delay_reasons[] = 'تأخير في إرجاع الفارغ';
                $days_overdue = max($days_overdue, $days_since_delivery - 7);
            }
        }

        if (!empty($delay_reasons)) {
            $delayed_containers[] = [
                'container_number' => $row['container_number'] ?: 'غير محدد',
                'client_name' => $row['client_name'],
                'delay_types' => implode(' / ', $delay_reasons),
                'shipping_date' => $row['entry_date'],
                'days_overdue' => $days_overdue
            ];
        }
    }
}

// Define fields with labels
$fields = [
    'container_number' => 'رقم الحاوية',
    'carrier' => 'الشركة الناقلة',
    'client_name' => 'اسم العميل',
    'code' => 'رقم العميل',
    'bill_number' => 'رقم البوليصة',
    'category' => 'الصنف',
    'registry' => 'السجل',
    'expected_arrival' => 'تاريخ الوصول المتوقع',
    'ship_name' => 'الباخرة',
    'custom_station' => 'المحطة الجمركية',
    'release_status' => 'تم الإفراج',
    'company_release' => 'تم الإفراج من الشركة',
    'bill_of_lading_status' => 'حالة البوليصة',
    'tashitim_status' => 'حالة التختيم',
    'loading_no' => 'رقم التحميل',
    'loading_number' => 'رقم التحميل',
    'office' => 'المكتب المصدر'
];

// Get filters from URL
$filters = [
    'field1' => $_GET['field1'] ?? '',
    'value1' => $_GET['value1'] ?? '',
    'field2' => $_GET['field2'] ?? '',
    'value2' => $_GET['value2'] ?? '',
    'field3' => $_GET['field3'] ?? '',
    'value3' => $_GET['value3'] ?? '',
    'field4' => $_GET['field4'] ?? '',
    'value4' => $_GET['value4'] ?? '',
    'field5' => $_GET['field5'] ?? '',
    'value5' => $_GET['value5'] ?? '',
    'from' => $_GET['from'] ?? '',
    'to' => $_GET['to'] ?? '',
    'source' => $_GET['source'] ?? '',
    'new_only' => isset($_GET['new_only']) ? '1' : ''
];

// Column sorting
$order_by = $_GET['order_by'] ?? 'id';
$order_dir = $_GET['order_dir'] ?? 'DESC';

// Validate order_by
$valid_order_fields = array_merge(array_keys($fields), ['entry_date', 'id']);
if (!in_array($order_by, $valid_order_fields)) {
    $order_by = 'id';
}
$order_dir = strtoupper($order_dir);
if (!in_array($order_dir, ['ASC', 'DESC'])) {
    $order_dir = 'DESC';
}

// Pagination
$items_per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $items_per_page;

function getOptions($field, $conn) {
    $opts = [];
    if (!empty($field)) {
        if ($field === 'bill_of_lading_status') {
            return ['not_issued' => 'لم يتم الإصدار', 'issued' => 'تم الإصدار', 'delayed' => 'متأخر'];
        } elseif ($field === 'tashitim_status') {
            return ['not_done' => 'لم يتم التختيم', 'done' => 'تم التختيم', 'delayed' => 'متأخر'];
        } elseif ($field === 'office') {
            return ['الصين' => 'الصين', 'محلي' => 'محلي'];
        }
        
        // Ensure the field is valid before using in query
        $valid_fields = ['container_number', 'carrier', 'client_name', 'code', 'bill_number', 
                        'category', 'registry', 'ship_name', 'custom_station', 
                        'release_status', 'company_release', 'loading_no', 'loading_number'];
        
        if (in_array($field, $valid_fields)) {
            $stmt = $conn->prepare("SELECT DISTINCT `$field` as val FROM containers WHERE `$field` IS NOT NULL AND `$field` != '' ORDER BY `$field` ASC");
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $val = $r['val'];
                    $opts[] = $val;
                }
                $stmt->close();
            }
        }
    }
    return $opts;
}

// Build WHERE clauses
$whereClauses = [];
$params = [];
$types = "";

// Process filters
for ($i = 1; $i <= 5; $i++) {
    $field = $filters["field$i"];
    $value = $filters["value$i"];
    if ($field && $value && array_key_exists($field, $fields)) {
        $whereClauses[] = "`$field` LIKE ?";
        $params[] = "%$value%";
        $types .= "s";
    }
}

// Date filters
if ($filters['from'] && $filters['to']) {
    $whereClauses[] = "entry_date BETWEEN ? AND ?";
    $params[] = $filters['from'];
    $params[] = $filters['to'];
    $types .= "ss";
} elseif ($filters['from']) {
    $whereClauses[] = "entry_date >= ?";
    $params[] = $filters['from'];
    $types .= "s";
} elseif ($filters['to']) {
    $whereClauses[] = "entry_date <= ?";
    $params[] = $filters['to'];
    $types .= "s";
}

// Source filter
if ($filters['source'] === 'china') {
    $whereClauses[] = "china_loading_id IS NOT NULL";
} elseif ($filters['source'] === 'local') {
    $whereClauses[] = "china_loading_id IS NULL";
}

// New containers only filter
if ($filters['new_only'] === '1' && $_SESSION['office'] === 'بورتسودان') {
    $whereClauses[] = "china_loading_id IS NOT NULL AND seen_by_port = 0";
}

$whereSql = "1";
if (count($whereClauses) > 0) {
    $whereSql = implode(" AND ", $whereClauses);
}

// Count total items
$countSql = "SELECT COUNT(*) as total FROM containers WHERE $whereSql";
$countStmt = $conn->prepare($countSql);
if ($countStmt) {
    if (count($params) > 0) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countRes = $countStmt->get_result()->fetch_assoc();
    $total_items = $countRes['total'];
    $countStmt->close();
} else {
    die("خطأ في الاستعلام (عدد النتائج): " . $conn->error);
}

// Main query
$sql = "SELECT containers.*, registers.name AS registry_name,
               COALESCE(containers.loading_no, containers.loading_number) AS display_loading_no,
               CASE WHEN china_loading_id IS NOT NULL THEN 'الصين' ELSE 'محلي' END as source_office,
               (SELECT status FROM container_position_history 
                WHERE container_id = containers.id 
                ORDER BY created_at DESC 
                LIMIT 1) AS latest_position
        FROM containers 
        LEFT JOIN registers ON containers.registry = registers.id 
        WHERE $whereSql 
        ORDER BY 
            CASE WHEN china_loading_id IS NOT NULL AND seen_by_port = 0 THEN 0 ELSE 1 END,
            `$order_by` $order_dir 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (count($params) > 0) {
        $params[] = $items_per_page;
        $params[] = $offset;
        $full_types = $types . "ii";
        $stmt->bind_param($full_types, ...$params);
    } else {
        $stmt->bind_param("ii", $items_per_page, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("خطأ في الاستعلام: " . $conn->error);
}

function sort_link($label, $field, $current_order_by, $current_order_dir, $extra_query) {
    $dir = "ASC";
    $arrow = "";
    if ($current_order_by === $field) {
        if ($current_order_dir === "ASC") {
            $dir = "DESC";
            $arrow = "▲";
        } else {
            $dir = "ASC";
            $arrow = "▼";
        }
    }
    $qs = http_build_query(array_merge($extra_query, ['order_by' => $field, 'order_dir' => $dir, 'page' => 1]));
    return "<a href=\"?$qs\">$label $arrow</a>";
}

function displayValue($field, $value) {
    if ($field === 'release_status' || $field === 'company_release') {
        return $value === 'Yes' ? 'نعم' : 'لا';
    }
    return htmlspecialchars($value ?: 'غير محدد');
}

function displayStatus($status, $type) {
    $statusLabels = [
        'bill_of_lading' => [
            'not_issued' => 'لم يتم الإصدار',
            'issued' => 'تم الإصدار',
            'delayed' => 'متأخر'
        ],
        'tashitim' => [
            'not_done' => 'لم يتم التختيم',
            'done' => 'تم التختيم',
            'delayed' => 'متأخر'
        ]
    ];
    
    $label = $statusLabels[$type][$status] ?? $status;
    $class = '';
    
    if ($status === 'delayed') {
        $class = 'bg-danger';
    } elseif ($status === 'issued' || $status === 'done') {
        $class = 'bg-success';
    } else {
        $class = 'bg-secondary';
    }
    
    return "<span class='badge $class'>$label</span>";
}

function translatePositionStatus($status) {
    $translations = [
        'Loaded' => 'تم التحميل',
        'At Port' => 'في الميناء',
        'At Sea' => 'في البحر',
        'Arrived' => 'وصلت',
        'Transported by Land' => 'تم الشحن البري',
        'Delivered' => 'تم التسليم',
        'Empty Returned' => 'تم تسليم الفارغ'
    ];
    return $translations[$status] ?? $status;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الحاويات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Cairo', sans-serif; 
            background: #f9f9f9; 
        }
        .header {
            background-color: #711739;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h4 {
            margin: 0;
            font-weight: bold;
        }
        .table {
            box-shadow: 0 0 5px #ccc;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: #711739;
            color: white;
        }
        .table th {
            border: none;
            padding: 15px;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }
        .table td {
            text-align: center;
            vertical-align: middle;
            padding: 12px;
        }
        .btn-action {
            margin: 0 2px;
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 5px #ccc;
            border-left: 4px solid #711739;
        }
        .badge {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        .bg-danger { background: #dc3545 !important; }
        .bg-warning { background: #ffc107 !important; }
        .bg-secondary { background: #6c757d !important; }
        .bg-success { background: #28a745 !important; }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .row-warning {
            background-color: #fff3cd !important;
        }
        .row-danger {
            background-color: #f8d7da !important;
        }
        .china-notification {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .new-china-container {
            background-color: #fffbf0;
            border-right: 4px solid #ffc107;
        }
        .china-badge {
            background-color: #17a2b8 !important;
            font-size: 0.75rem;
        }
        .new-badge {
            background-color: #ffc107 !important;
            color: #212529 !important;
            font-size: 0.75rem;
            margin-right: 5px;
        }
        #delayModal .modal-content {
            border-radius: 10px;
            border: 2px solid #711739;
        }
        #delayModal .modal-header {
            background-color: #711739;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        #delayModal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        .delay-table {
            width: 100%;
            margin-top: 15px;
        }
        .delay-table th {
            background-color: #711739;
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: 500;
        }
        .delay-table td {
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        .delay-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .delay-table tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.2s;
        }
        .delay-summary {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8d7da;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .delay-summary h5 {
            color: #721c24;
            margin: 0;
            font-weight: bold;
        }
        .btn-close {
            background-color: white;
            opacity: 0.8;
        }
        .btn-close:hover {
            opacity: 1;
        }
        .sync-info {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 10px;
            }
            .filter-group {
                min-width: 100%;
            }
            .table {
                font-size: 0.875rem;
            }
            .btn-action {
                padding: 3px 6px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<?php if ($newChinaContainers > 0 && $_SESSION['office'] === 'بورتسودان'): ?>
<div class="china-notification">
    <div>
        <i class="bi bi-bell-fill"></i>
        <strong>تنبيه:</strong> يوجد <?= $newChinaContainers ?> حاوية جديدة من مكتب الصين لم يتم مراجعتها
    </div>
    <a href="?new_only=1" class="btn btn-warning btn-sm">
        <i class="bi bi-eye"></i> عرض الحاويات الجديدة فقط
    </a>
</div>
<?php endif; ?>

<?php if (!empty($delayed_containers)): ?>
<div class="modal fade" id="delayModal" tabindex="-1" aria-labelledby="delayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delayModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> تنبيه: الحاويات المتأخرة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="delay-summary">
                    <h5><i class="bi bi-info-circle"></i> يوجد <?= count($delayed_containers) ?> حاوية متأخرة</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered delay-table">
                        <thead>
                            <tr>
                                <th>رقم الحاوية</th>
                                <th>اسم العميل</th>
                                <th>نوع التأخير</th>
                                <th>تاريخ الدخول</th>
                                <th>أيام التأخير</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delayed_containers as $container): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($container['container_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($container['client_name']) ?></td>
                                    <td class="text-danger"><?= htmlspecialchars($container['delay_types']) ?></td>
                                    <td><?= htmlspecialchars($container['shipping_date']) ?></td>
                                    <td><span class="badge bg-danger"><?= $container['days_overdue'] ?> يوم</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> إغلاق
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="header">
    <h4><i class="bi bi-box"></i> قائمة الحاويات</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="add_container.php" class="btn btn-success btn-sm"><i class="bi bi-plus"></i> إضافة حاوية</a>
        <a href="dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-house"></i> الرئيسية</a>
        <a href="export_excel.php" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> تصدير Excel</a>
        <?php if ($_SESSION['office'] === 'بورتسودان'): ?>
        <a href="sync_status.php" class="btn btn-info btn-sm"><i class="bi bi-arrow-repeat"></i> حالة المزامنة</a>
        <?php endif; ?>
    </div>
</div>

<div class="filter-section">
    <h5 class="mb-3"><i class="bi bi-funnel"></i> البحث والتصفية</h5>
    <form method="GET" id="filterForm">
        <div class="filter-row">
            <?php for($i = 1; $i <= 5; $i++): ?>
            <div class="filter-group">
                <label>الحقل <?= $i ?>:</label>
                <select name="field<?= $i ?>" class="form-select filter-field" data-index="<?= $i ?>">
                    <option value="">-- اختر الحقل --</option>
                    <?php foreach($fields as $fk => $fv): ?>
                        <option value="<?= $fk ?>" <?= $filters["field$i"] === $fk ? 'selected' : '' ?>><?= $fv ?></option>
                    <?php endforeach; ?>
                </select>
                
                <?php if($filters["field$i"]): ?>
                    <?php $opts = getOptions($filters["field$i"], $conn); ?>
                    <?php if(count($opts) > 0): ?>
                        <select name="value<?= $i ?>" class="form-select mt-2">
                            <option value="">-- اختر القيمة --</option>
                            <?php foreach($opts as $opt_val): ?>
                                <option value="<?= htmlspecialchars($opt_val) ?>" <?= $filters["value$i"] === $opt_val ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opt_val) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="value<?= $i ?>" class="form-control mt-2" placeholder="أدخل القيمة" value="<?= htmlspecialchars($filters["value$i"]) ?>">
                    <?php endif; ?>
                <?php else: ?>
                    <input type="text" name="value<?= $i ?>" class="form-control mt-2" placeholder="أدخل القيمة" value="<?= htmlspecialchars($filters["value$i"]) ?>" style="display: none;">
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label>من تاريخ:</label>
                <input type="date" name="from" class="form-control" value="<?= $filters['from'] ?>">
            </div>
            <div class="filter-group">
                <label>إلى تاريخ:</label>
                <input type="date" name="to" class="form-control" value="<?= $filters['to'] ?>">
            </div>
            <div class="filter-group">
                <label>المصدر:</label>
                <select name="source" class="form-select">
                    <option value="">-- الكل --</option>
                    <option value="china" <?= $filters['source'] === 'china' ? 'selected' : '' ?>>من الصين</option>
                    <option value="local" <?= $filters['source'] === 'local' ? 'selected' : '' ?>>محلي</option>
                </select>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> بحث</button>
            <a href="containers.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> إعادة تعيين</a>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="8%"><?= sort_link('تاريخ الدخول', 'entry_date', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('اسم العميل', 'client_name', $order_by, $order_dir, $filters) ?></th>
                <th width="8%"><?= sort_link('رقم العميل', 'code', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('رقم التحميل', 'loading_no', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('رقم الحاوية', 'container_number', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('رقم البوليصة', 'bill_number', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('الصنف', 'category', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('الشركة الناقلة', 'carrier', $order_by, $order_dir, $filters) ?></th>
                <th>السجل</th>
                <th><?= sort_link('الوصول المتوقع', 'expected_arrival', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('الباخرة', 'ship_name', $order_by, $order_dir, $filters) ?></th>
                <th><?= sort_link('المحطة الجمركية', 'custom_station', $order_by, $order_dir, $filters) ?></th>
                <th>الموقع الحالي</th>
                <th>المصدر</th>
                <th>تم الإفراج</th>
                <th>إفراج الشركة</th>
                <th>حالة البوليصة</th>
                <th>حالة التختيم</th>
                <th>الإجراءات</th>
                <th>تحديث الحالة</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="21" class="text-center py-4">لا توجد نتائج مطابقة</td></tr>
            <?php else: ?>
                <?php $i = $offset + 1; while($r = $result->fetch_assoc()): 
                    $isNewFromChina = ($r['china_loading_id'] && !$r['seen_by_port'] && $_SESSION['office'] === 'بورتسودان');
                    $isFromChina = !empty($r['china_loading_id']);
                    
                    // Calculate delays
                    $is_delayed = false;
                    $is_warning = false;
                    
                    if ($r['bill_of_lading_status'] == 'not_issued' && $r['entry_date']) {
                        $days_since_entry = (new DateTime())->diff(new DateTime($r['entry_date']))->days;
                        $days_remaining = 10 - $days_since_entry;
                        if ($days_remaining < 0) {
                            $is_delayed = true;
                        } elseif ($days_remaining <= 2) {
                            $is_warning = true;
                        }
                    }
                    
                    if ($r['tashitim_status'] == 'not_done' && $r['bill_of_lading_date']) {
                        $days_since_bill = (new DateTime())->diff(new DateTime($r['bill_of_lading_date']))->days;
                        $days_remaining = 20 - $days_since_bill;
                        if ($days_remaining < 0) {
                            $is_delayed = true;
                        } elseif ($days_remaining <= 2) {
                            $is_warning = true;
                        }
                    }
                    
                    if ($r['bill_of_lading_status'] == 'delayed' || $r['tashitim_status'] == 'delayed') {
                        $is_delayed = true;
                    }
                    
                    $row_class = '';
                    if ($isNewFromChina) {
                        $row_class = 'new-china-container';
                    } elseif ($is_delayed) {
                        $row_class = 'row-danger';
                    } elseif ($is_warning) {
                        $row_class = 'row-warning';
                    }
                ?>
                    <tr class="<?= $row_class ?>">
                        <td><?= $i++ ?></td>
                        <td><?= displayValue('entry_date', $r['entry_date']) ?></td>
                        <td>
                            <?= displayValue('client_name', $r['client_name']) ?>
                            <?php if ($isNewFromChina): ?>
                                <span class="badge new-badge">جديد</span>
                            <?php endif; ?>
                        </td>
                        <td><?= displayValue('code', $r['code']) ?></td>
                        <td><?= displayValue('loading_no', $r['display_loading_no']) ?></td>
                        <td><?= displayValue('container_number', $r['container_number']) ?></td>
                        <td><?= displayValue('bill_number', $r['bill_number']) ?></td>
                        <td><?= displayValue('category', $r['category']) ?></td>
                        <td><?= displayValue('carrier', $r['carrier']) ?></td>
                        <td><?= displayValue('registry', $r['registry_name']) ?></td>
                        <td><?= displayValue('expected_arrival', $r['expected_arrival']) ?></td>
                        <td><?= displayValue('ship_name', $r['ship_name']) ?></td>
                        <td><?= displayValue('custom_station', $r['custom_station']) ?></td>
                        <td><?= !empty($r['latest_position']) ? translatePositionStatus($r['latest_position']) : 'غير متوفر' ?></td>
                        <td>
                            <?php if ($isFromChina): ?>
                                <span class="badge china-badge"><i class="bi bi-globe"></i> <?= $r['source_office'] ?></span>
                                <?php if ($r['china_loading_id']): ?>
                                    <div class="sync-info">ID: <?= $r['china_loading_id'] ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">محلي</span>
                            <?php endif; ?>
                        </td>
                        <td><?= displayValue('release_status', $r['release_status']) ?></td>
                        <td><?= displayValue('company_release', $r['company_release']) ?></td>
                        <td><?= displayStatus($r['bill_of_lading_status'], 'bill_of_lading') ?></td>
                        <td><?= displayStatus($r['tashitim_status'], 'tashitim') ?></td>
                        <td class="d-flex justify-content-center">
                            <a href="view_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary btn-action" title="عرض"><i class="bi bi-eye"></i></a>
                            <?php if (!$isFromChina): ?>
                                <a href="edit_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning btn-action" title="تعديل"><i class="bi bi-pencil"></i></a>
                                <a href="delete_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger btn-action" title="حذف" onclick="return confirm('تأكيد الحذف؟')"><i class="bi bi-trash"></i></a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary btn-action" disabled title="يجب التعديل من نظام الصين">
                                    <i class="bi bi-pencil-slash"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($isNewFromChina): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="container_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="mark_seen" class="btn btn-sm btn-success btn-action" title="تم المراجعة">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="update_container_status.php?container_id=<?= $r['id'] ?>" class="btn btn-sm btn-info btn-action" title="تحديث حالة الحاوية">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php
        $total_pages = ceil($total_items / $items_per_page);
        $base_query = $_GET;
        
        // Previous button
        if ($page > 1) {
            $base_query['page'] = $page - 1;
            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($base_query) . '">السابق</a></li>';
        }
        
        // Page numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            $base_query['page'] = 1;
            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($base_query) . '">1</a></li>';
            if ($start_page > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($p = $start_page; $p <= $end_page; $p++) {
            $base_query['page'] = $p;
            $page_url = '?' . http_build_query($base_query);
            $active = ($p === $page) ? 'active' : '';
            echo "<li class='page-item $active'><a class='page-link' href='$page_url'>$p</a></li>";
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $base_query['page'] = $total_pages;
            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($base_query) . '">' . $total_pages . '</a></li>';
        }
        
        // Next button
        if ($page < $total_pages) {
            $base_query['page'] = $page + 1;
            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($base_query) . '">التالي</a></li>';
        }
        ?>
    </ul>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show delay modal if there are delayed containers
        <?php if (!empty($delayed_containers)): ?>
            const delayModal = new bootstrap.Modal(document.getElementById('delayModal'));
            delayModal.show();
        <?php endif; ?>
        
        // Handle filter field changes
        document.querySelectorAll('.filter-field').forEach(select => {
            select.addEventListener('change', function() {
                const index = this.getAttribute('data-index');
                const valueInput = document.querySelector(`[name="value${index}"]`);
                
                if (this.value === '') {
                    valueInput.value = '';
                    valueInput.style.display = 'none';
                } else {
                    // Submit form to reload with new field options
                    document.getElementById('filterForm').submit();
                }
            });
        });
        
        // Auto-check for new containers every 60 seconds (Port Sudan only)
        <?php if ($_SESSION['office'] === 'بورتسودان' && !isset($_GET['new_only'])): ?>
        let lastCheckCount = <?= $newChinaContainers ?>;
        
        function checkNewContainers() {
            fetch('check_new_containers.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_containers > lastCheckCount) {
                        // New containers detected, show notification
                        if (confirm('تم اكتشاف حاويات جديدة من الصين. هل تريد تحديث الصفحة؟')) {
                            location.reload();
                        }
                        lastCheckCount = data.new_containers;
                    }
                })
                .catch(error => console.error('Error checking new containers:', error));
        }
        
        // Check every 60 seconds
        setInterval(checkNewContainers, 60000);
        <?php endif; ?>
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>