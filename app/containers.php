<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'auth.php';
include 'config.php';

// Get delayed containers with all status checks
$delayed_containers = [];
$delayed_query = $conn->query("
    SELECT 
        c.id, 
        c.container_number, 
        c.client_name, 
        c.entry_date,
        c.bill_of_lading_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Arrived') AS arrival_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Transported by Land') AS land_transport_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Delivered') AS delivery_date,
        (SELECT MIN(created_at) FROM container_position_history 
         WHERE container_id = c.id AND status = 'Empty Returned') AS empty_return_date
    FROM containers c
    WHERE c.release_status = 'No'
");

if ($delayed_query->num_rows > 0) {
    while ($row = $delayed_query->fetch_assoc()) {
        $delay_reasons = [];
        $days_overdue = 0;
        $current_date = new DateTime();

        // Bill of Lading delay (10 days from entry_date, delayed on day 11+)
        if ($row['entry_date'] && !$row['bill_of_lading_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 10) {
                $delay_reasons[] = 'تأخير في إصدار البوليصة';
                $days_overdue = max($days_overdue, $days_since_entry - 10);
            }
        }

        // Tashitim delay (20 days from bill_of_lading_date, delayed on day 21+)
        if ($row['bill_of_lading_date'] && !$row['tashitim_date']) {
            $bl_date = new DateTime($row['bill_of_lading_date']);
            $days_since_bl = $current_date->diff($bl_date)->days;
            if ($days_since_bl > 20) {
                $delay_reasons[] = 'تأخير في التختيم';
                $days_overdue = max($days_overdue, $days_since_bl - 20);
            }
        }

        // Arrival delay (60 days from entry_date, delayed on day 61+)
        if ($row['entry_date'] && !$row['arrival_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 60) {
                $delay_reasons[] = 'تأخير في الوصول';
                $days_overdue = max($days_overdue, $days_since_entry - 60);
            }
        }

        // Land Transport delay (4 days from arrival_date, delayed on day 5+)
        if ($row['arrival_date'] && !$row['land_transport_date']) {
            $arrival_date = new DateTime($row['arrival_date']);
            $days_since_arrival = $current_date->diff($arrival_date)->days;
            if ($days_since_arrival > 4) {
                $delay_reasons[] = 'تأخير في الشحن';
                $days_overdue = max($days_overdue, $days_since_arrival - 4);
            }
        }

        // Delivery delay (3 days from entry_date, delayed on day 4+)
        if ($row['entry_date'] && !$row['delivery_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 3) {
                $delay_reasons[] = 'تأخير في التسليم';
                $days_overdue = max($days_overdue, $days_since_entry - 3);
            }
        }

        // Empty Return delay (7 days from entry_date, delayed on day 8+)
        if ($row['entry_date'] && !$row['empty_return_date']) {
            $entry_date = new DateTime($row['entry_date']);
            $days_since_entry = $current_date->diff($entry_date)->days;
            if ($days_since_entry > 7) {
                $delay_reasons[] = 'تأخير في تسليم الفارغ';
                $days_overdue = max($days_overdue, $days_since_entry - 7);
            }
        }

        // Only add to delayed_containers if there are actual delays
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
    'tashitim_status' => 'حالة التختيم'
];

// Get filters, sorting, and pagination from URL with defaults
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
];

// Column sorting
$order_by = $_GET['order_by'] ?? 'id';
$order_dir = $_GET['order_dir'] ?? 'DESC';

if (!array_key_exists($order_by, $fields) && $order_by !== 'entry_date' && $order_by !== 'id') {
    $order_by = 'id'; // Default
}
$order_dir = strtoupper($order_dir);
if (!in_array($order_dir, ['ASC', 'DESC'])) {
    $order_dir = 'DESC';
}

// Pagination
$items_per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $items_per_page;

// Function to get filter options for each field
function getOptions($field, $conn) {
    $opts = [];
    if (!empty($field)) {
        if ($field === 'bill_of_lading_status') {
            return ['not_issued' => 'لم يتم الإصدار', 'issued' => 'تم الإصدار', 'delayed' => 'متأخر'];
        } elseif ($field === 'tashitim_status') {
            return ['not_done' => 'لم يتم التختيم', 'done' => 'تم التختيم', 'delayed' => 'متأخر'];
        }
        
        $stmt = $conn->prepare("SELECT DISTINCT `$field` as val FROM containers ORDER BY `$field` ASC");
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $val = $r['val'] !== '' ? $r['val'] : 'غير محدد';
                $opts[] = $val;
            }
            $stmt->close();
        }
    }
    return $opts;
}

// Build WHERE conditions with Prepared Statements
$whereClauses = [];
$params = [];
$types = "";

for ($i = 1; $i <= 5; $i++) {
    $field = $filters["field$i"];
    $value = $filters["value$i"];
    if ($field && $value && array_key_exists($field, $fields)) {
        $whereClauses[] = "`$field` LIKE ?";
        $params[] = "%$value%";
        $types .= "s";
    }
}

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

$whereSql = "1";
if (count($whereClauses) > 0) {
    $whereSql = implode(" AND ", $whereClauses);
}

// Count total results for pagination
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

// Query data with sorting and pagination
$sql = "SELECT containers.*, registers.name AS registry_name, 
               (SELECT status FROM container_position_history 
                WHERE container_id = containers.id 
                ORDER BY created_at DESC 
                LIMIT 1) AS latest_position
        FROM containers 
        LEFT JOIN registers ON containers.registry = registers.id 
        WHERE $whereSql 
        ORDER BY `$order_by` $order_dir 
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

// Function to generate sort links with ASC/DESC toggle
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
    return "<a href=\"?{$qs}\" style=\"color:inherit; text-decoration:none;\">{$label} {$arrow}</a>";
}

// Function to display values with enhancements (✅❌ and "غير متوفر")
function displayValue($field, $value) {
    if ($field === 'release_status' || $field === 'company_release') {
        if ($value === '1' || strtolower($value) === 'yes' || strtolower($value) === 'نعم') return "✅";
        if ($value === '0' || strtolower($value) === 'no' || strtolower($value) === 'لا') return "❌";
        return htmlspecialchars($value);
    }
    if ($value === '' || $value === null) {
        return "غير متوفر";
    }
    return htmlspecialchars($value);
}

// Function to display status with colors
function displayStatus($status, $type) {
    $statusLabels = [
        'bill_of_lading' => [
            'not_issued' => 'لم يتم',
            'issued' => 'تم الإصدار',
            'delayed' => 'متأخر'
        ],
        'tashitim' => [
            'not_done' => 'لم يتم',
            'done' => 'تم',
            'delayed' => 'متأخر'
        ]
    ];
    
    $class = '';
    $text = '';
    
    if ($type === 'bill_of_lading') {
        $text = $statusLabels[$type][$status] ?? $status;
        $class = $status === 'issued' ? 'bg-success' : 
                 ($status === 'delayed' ? 'bg-danger' : 'bg-secondary');
    } else {
        $text = $statusLabels[$type][$status] ?? $status;
        $class = $status === 'done' ? 'bg-success' : 
                 ($status === 'delayed' ? 'bg-danger' : 'bg-secondary');
    }
    
    return "<span class=\"badge $class\">$text</span>";
}

// Translate position status
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

// Fetch container statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN release_status = 'Yes' THEN 1 ELSE 0 END) as released,
        SUM(CASE WHEN company_release = 'Yes' THEN 1 ELSE 0 END) as company_released
    FROM containers
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>📦 قائمة الحاويات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #711739;
            --secondary-color: #8a1c47;
            --light-bg: #f9f9f9;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        body { 
            font-family: 'Cairo', sans-serif; 
            background: var(--light-bg); 
            padding: 20px; 
            color: #333;
        }
        
        .header { 
            background: var(--primary-color); 
            color: white; 
            padding: 15px 25px; 
            border-radius: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            flex-wrap: wrap;
            box-shadow: var(--card-shadow);
        }
        
        .header h4 { 
            margin: 0; 
            font-weight: bold; 
            font-size: 1.5rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9rem;
        }
        
        table th { 
            background: var(--primary-color); 
            color: white; 
            font-size: 14px; 
            cursor: pointer; 
            user-select: none; 
            vertical-align: middle;
        }
        
        select, input[type=date], input[type=text] { 
            font-size: 14px; 
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 8px 12px;
        }
        
        .btn-action {
            margin: 0 3px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .quick-search-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .quick-search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .quick-search-container input {
            padding-left: 40px;
        }
        
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { font-size: 12px; }
            .header { flex-direction: column; gap: 15px; }
            .header h4 { text-align: center; }
            .header > div { justify-content: center; }
            .stats-container { grid-template-columns: 1fr; }
            .filter-row > div { margin-bottom: 10px; }
        }
        
        .badge {
            font-weight: normal;
            padding: 5px 8px;
            border-radius: 4px;
        }
        .bg-success { background: #d4edda !important; color: #155724; }
        .bg-danger { background: #f8d7da !important; color: #721c24; }
        .bg-warning { background: #fff3cd !important; color: #856404; }
        .bg-secondary { background: #e9ecef !important; color: #495057; }
        
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
        
        /* Popup Styles */
        #delayModal .modal-content {
            border-radius: 10px;
            border: 2px solid #dc3545;
        }
        
        #delayModal .modal-header {
            background-color: #dc3545;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        #delayModal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .delay-table th {
            background-color: #dc3545;
            color: white;
        }
        
        .delay-table tr:nth-child(even) {
            background-color: #f8d7da;
        }
        
        .delay-table tr:hover {
            background-color: #f5c2c7;
        }
    </style>
</head>
<body>

<!-- Delayed Containers Popup -->
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
                <div class="table-responsive">
                    <table class="table table-bordered delay-table">
                        <thead>
                            <tr>
                                <th>رقم الحاوية</th>
                                <th>اسم العميل</th>
                                <th>نوع التأخير</th>
                                <th>تاريخ الشحن</th>
                                <th>أيام التأخير</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delayed_containers as $container): ?>
                                <tr>
                                    <td><?= htmlspecialchars($container['container_number']) ?></td>
                                    <td><?= htmlspecialchars($container['client_name']) ?></td>
                                    <td><?= htmlspecialchars($container['delay_types']) ?></td>
                                    <td><?= htmlspecialchars($container['shipping_date']) ?></td>
                                    <td class="text-danger fw-bold"><?= $container['days_overdue'] ?> يوم</td>
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
        <a href="export_excel.php" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
    </div>
</div>

<!-- Container Statistics -->
<div class="stats-container">
    <div class="stat-card">
        <div class="stat-title">إجمالي الحاويات</div>
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div><i class="bi bi-boxes" style="font-size: 1.5rem;"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">تم الإفراج</div>
        <div class="stat-number"><?= $stats['released'] ?></div>
        <div><i class="bi bi-check-circle" style="font-size: 1.5rem;"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">تم الإفراج من الشركة</div>
        <div class="stat-number"><?= $stats['company_released'] ?></div>
        <div><i class="bi bi-building-check" style="font-size: 1.5rem;"></i></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-title">الحاويات النشطة</div>
        <div class="stat-number"><?= $stats['total'] - $stats['released'] ?></div>
        <div><i class="bi bi-activity" style="font-size: 1.5rem;"></i></div>
    </div>
</div>

<form method="GET" id="filterForm">
    <!-- Main Filter Row -->
    <div class="filter-row">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="filter-group">
                <div class="d-flex">
                    <?php
                        $used = array_filter([$filters['field1'], $filters['field2'], $filters['field3'], $filters['field4'], $filters['field5']]);
                        $fieldKey = "field$i";
                        $valueKey = "value$i";
                    ?>
                    <select name="<?= $fieldKey ?>" class="form-select filter-field" data-index="<?= $i ?>" style="width: 45%; margin-right: 5px;">
                        <option value="">فلتر <?= $i ?></option>
                        <?php foreach ($fields as $key => $label): ?>
                            <?php if (!in_array($key, array_diff($used, [$filters[$fieldKey]]))): ?>
                                <option value="<?= $key ?>" <?= ($filters[$fieldKey] == $key ? 'selected' : '') ?>><?= $label ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if ($filters[$fieldKey]): ?>
                        <?php $options = getOptions($filters[$fieldKey], $conn); ?>
                        <?php if (is_array($options) && !empty($options)): ?>
                            <select name="<?= $valueKey ?>" class="form-select" style="width: 45%;">
                                <option value="">-- اختر --</option>
                                <?php foreach ($options as $optKey => $optVal): ?>
                                    <?php if (is_array($options) && array_keys($options) !== range(0, count($options) - 1)): ?>
                                        <option value="<?= htmlspecialchars($optKey) ?>" <?= ($filters[$valueKey] == $optKey ? 'selected' : '') ?>><?= htmlspecialchars($optVal) ?></option>
                                    <?php else: ?>
                                        <option value="<?= htmlspecialchars($optVal) ?>" <?= ($filters[$valueKey] == $optVal ? 'selected' : '') ?>><?= htmlspecialchars($optVal) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="<?= $valueKey ?>" class="form-control" value="<?= htmlspecialchars($filters[$valueKey]) ?>" placeholder="اكتب للبحث" style="width: 45%;" />
                        <?php endif; ?>
                    <?php else: ?>
                        <input type="text" name="<?= $valueKey ?>" class="form-control" value="<?= htmlspecialchars($filters[$valueKey]) ?>" placeholder="اكتب للبحث" style="width: 45%;" />
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    
    <!-- Additional Filter Row -->
    <div class="filter-row">
        <div class="filter-group">
            <div class="d-flex">
                <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>" class="form-control" placeholder="من تاريخ" style="width: 45%; margin-right: 5px;">
                <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>" class="form-control" placeholder="إلى تاريخ" style="width: 45%;">
            </div>
        </div>
        
        <div class="filter-group">
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> فلتر</button>
                <a href="containers.php" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> إزالة</a>
            </div>
        </div>
    </div>
</form>

<div class="mb-3">
    <strong>عدد النتائج: <?= $total_items ?></strong>
</div>

<div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th><?= sort_link('تاريخ الشحن', 'entry_date', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('اسم العميل', 'client_name', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('رقم العميل', 'code', $order_by, $order_dir, $_GET) ?></th>
                <th>لودنق</th>
                <th><?= sort_link('رقم الحاوية', 'container_number', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('رقم البوليصة', 'bill_number', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('الصنف', 'category', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('الشركة الناقلة', 'carrier', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('السجل', 'registry', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('تاريخ الوصول المتوقع', 'expected_arrival', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('الباخرة', 'ship_name', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('المحطة الجمركية', 'custom_station', $order_by, $order_dir, $_GET) ?></th>
                <th>حالة الحاوية (الموقع)</th>
                <th><?= sort_link('تم الإفراج', 'release_status', $order_by, $order_dir, $_GET) ?></th>
                <th><?= sort_link('تم الإفراج من الشركة', 'company_release', $order_by, $order_dir, $_GET) ?></th>
                <th>حالة البوليصة</th>
                <th>حالة التختيم</th>
                <th>الإجراء</th>
                <th>تحديث الحالة</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="20" class="text-center py-4">لا توجد نتائج مطابقة</td></tr>
            <?php else: ?>
                <?php $i = $offset + 1; while($r = $result->fetch_assoc()): 
                    // Calculate status delays
                    $is_delayed = false;
                    $is_warning = false;
                    
                    // Bill of Lading delay check
                    if ($r['bill_of_lading_status'] == 'not_issued' && $r['entry_date']) {
                        $days_since_entry = (new DateTime())->diff(new DateTime($r['entry_date']))->days;
                        $days_remaining = 10 - $days_since_entry;
                        
                        if ($days_remaining <= 0) {
                            $is_delayed = true;
                        } elseif ($days_remaining <= 2) {
                            $is_warning = true;
                        }
                    }
                    
                    // Tashitim delay check
                    if ($r['tashitim_status'] == 'not_done' && $r['bill_of_lading_date']) {
                        $days_since_bill = (new DateTime())->diff(new DateTime($r['bill_of_lading_date']))->days;
                        $days_remaining = 20 - $days_since_bill;
                        
                        if ($days_remaining <= 0) {
                            $is_delayed = true;
                        } elseif ($days_remaining <= 2) {
                            $is_warning = true;
                        }
                    }
                    
                    if ($r['bill_of_lading_status'] == 'delayed' || $r['tashitim_status'] == 'delayed') {
                        $is_delayed = true;
                    }
                    
                    $row_class = '';
                    if ($is_delayed) {
                        $row_class = 'row-danger';
                    } elseif ($is_warning) {
                        $row_class = 'row-warning';
                    }
                ?>
                    <tr class="<?= $row_class ?>">
                        <td><?= $i++ ?></td>
                        <td><?= displayValue('entry_date', $r['entry_date']) ?></td>
                        <td><?= displayValue('client_name', $r['client_name']) ?></td>
                        <td><?= displayValue('code', $r['code']) ?></td>
                        <td><?= displayValue('loading_number', $r['loading_number']) ?></td>
                        <td><?= displayValue('container_number', $r['container_number']) ?></td>
                        <td><?= displayValue('bill_number', $r['bill_number']) ?></td>
                        <td><?= displayValue('category', $r['category']) ?></td>
                        <td><?= displayValue('carrier', $r['carrier']) ?></td>
                        <td><?= displayValue('registry', $r['registry_name']) ?></td>
                        <td><?= displayValue('expected_arrival', $r['expected_arrival']) ?></td>
                        <td><?= displayValue('ship_name', $r['ship_name']) ?></td>
                        <td><?= displayValue('custom_station', $r['custom_station']) ?></td>
                        <td><?= !empty($r['latest_position']) ? translatePositionStatus($r['latest_position']) : 'غير متوفر' ?></td>
                        <td><?= displayValue('release_status', $r['release_status']) ?></td>
                        <td><?= displayValue('company_release', $r['company_release']) ?></td>
                        <td><?= displayStatus($r['bill_of_lading_status'], 'bill_of_lading') ?></td>
                        <td><?= displayStatus($r['tashitim_status'], 'tashitim') ?></td>
                        <td class="d-flex justify-content-center">
                            <a href="view_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary btn-action" title="عرض"><i class="bi bi-eye"></i></a>
                            <a href="edit_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning btn-action" title="تعديل"><i class="bi bi-pencil"></i></a>
                            <a href="delete_container.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger btn-action" title="حذف" onclick="return confirm('تأكيد الحذف؟')"><i class="bi bi-trash"></i></a>
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
        for ($p = 1; $p <= $total_pages; $p++) {
            $base_query['page'] = $p;
            $page_url = '?' . http_build_query($base_query);
            $active = ($p === $page) ? 'active' : '';
            echo "<li class='page-item $active'><a class='page-link' href='$page_url'>$p</a></li>";
        }
        ?>
    </ul>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show modal on page load if there are delayed containers
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($delayed_containers)): ?>
            const delayModal = new bootstrap.Modal(document.getElementById('delayModal'));
            delayModal.show();
        <?php endif; ?>
        
        // Reload filter options when field changes
        document.querySelectorAll('.filter-field').forEach(select => {
            select.addEventListener('change', function() {
                const index = this.getAttribute('data-index');
                const valueSelect = document.querySelector(`select[name="value${index}"], input[name="value${index}"]`);
                if (valueSelect) {
                    if (this.value === '') {
                        valueSelect.value = '';
                        valueSelect.style.display = 'inline-block';
                    } else {
                        document.getElementById('filterForm').submit();
                    }
                }
            });
        });
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>