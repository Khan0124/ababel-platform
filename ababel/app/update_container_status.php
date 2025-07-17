<?php
/**
 * Container Status Update - Visual Interface & API
 * Provides both visual status tracking and JSON API endpoints
 */

require_once 'config.php';
require_once 'auth.php';
require_once 'container_functions.php';
// Check if request is for API (AJAX/JSON)
$isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
$isApiRequest = $isApiRequest || (isset($_GET['format']) && $_GET['format'] === 'json');

// If API request, handle it separately
if ($isApiRequest) {
    handleApiRequest();
    exit;
}

// Visual Interface Code
$container_id = filter_input(INPUT_GET, 'container_id', FILTER_VALIDATE_INT) 
                ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$container_id) {
    header('Location: containers.php?error=invalid_id');
    exit;
}

// Get container data
$container = getContainerById($conn, $container_id);
if (!$container) {
    header('Location: containers.php?error=not_found');
    exit;
}

// Get all status data
$operational_statuses = getOperationalStatuses($conn, $container_id);
$position_history = getPositionHistory($conn, $container_id);
$timeline_events = getTimelineEvents($conn, $container_id);

// Calculate progress percentage
$progress = calculateProgress($container, $operational_statuses, $position_history);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة الحاوية - <?= htmlspecialchars($container['container_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #711739;
            --secondary-color: #99004d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f9f9f9;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--light-bg);
            color: #333;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .header-section h1 {
            margin: 0;
            font-weight: bold;
        }
        
        .container-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .progress-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }
        
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
            background: #e9ecef;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            transition: width 0.6s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .timeline-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }
        
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            right: 40px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #ddd;
        }
        
        .timeline-item {
            position: relative;
            padding-right: 80px;
            padding-bottom: 40px;
        }
        
        .timeline-icon {
            position: absolute;
            right: 25px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 3px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 1;
        }
        
        .timeline-item.completed .timeline-icon {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .timeline-item.pending .timeline-icon {
            background: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
        }
        
        .timeline-item.delayed .timeline-icon {
            background: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            border-right: 4px solid #ddd;
        }
        
        .timeline-item.completed .timeline-content {
            border-color: var(--success-color);
        }
        
        .timeline-item.pending .timeline-content {
            border-color: var(--warning-color);
        }
        
        .timeline-item.delayed .timeline-content {
            border-color: var(--danger-color);
        }
        
        .details-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }
        
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--primary-color);
            min-width: 150px;
            display: inline-block;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-delayed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .timeline-item {
                padding-right: 60px;
            }
            
            .timeline::before {
                right: 25px;
            }
            
            .timeline-icon {
                right: 10px;
            }
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header-section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-box-seam"></i> حالة الحاوية</h1>
                <div class="container-info">
                    <h4 class="mb-2">رقم الحاوية: <?= htmlspecialchars($container['container_number']) ?></h4>
                    <p class="mb-0">العميل: <?= htmlspecialchars($container['client_name']) ?> | التاريخ: <?= htmlspecialchars($container['entry_date']) ?></p>
                </div>
            </div>
            <div>
                <a href="containers.php" class="btn btn-light">
                    <i class="bi bi-arrow-right"></i> العودة للقائمة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Progress Bar -->
    <div class="progress-container">
        <h5 class="mb-3">نسبة الإنجاز الكلية</h5>
        <div class="progress-bar-custom">
            <div class="progress-fill" style="width: <?= $progress ?>%">
                <?= $progress ?>%
            </div>
        </div>
        <div class="mt-3 text-muted">
            <small>تم إنجاز <?= $progress ?>% من العمليات المطلوبة</small>
        </div>
    </div>

    <!-- Timeline -->
    <div class="timeline-container">
        <h5 class="mb-4"><i class="bi bi-clock-history"></i> المخطط الزمني للحاوية</h5>
        <div class="timeline">
            <?php foreach ($timeline_events as $event): ?>
            <div class="timeline-item <?= $event['status_class'] ?>">
                <div class="timeline-icon">
                    <i class="bi <?= $event['icon'] ?>"></i>
                </div>
                <div class="timeline-content">
                    <h6 class="mb-1"><?= $event['title'] ?></h6>
                    <p class="mb-1 text-muted"><?= $event['description'] ?></p>
                    <small class="text-muted">
                        <i class="bi bi-calendar3"></i> <?= $event['date'] ?: 'في الانتظار' ?>
                    </small>
                    <?php if ($event['file_path']): ?>
                        <a href="<?= $event['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-file-earmark"></i> عرض المستند
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row">
        <!-- Container Details -->
        <div class="col-md-6">
            <div class="details-card">
                <h5 class="mb-4"><i class="bi bi-info-circle"></i> تفاصيل الحاوية</h5>
                <div class="detail-item">
                    <span class="detail-label">رقم البوليصة:</span>
                    <span><?= htmlspecialchars($container['bill_number'] ?: 'غير محدد') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الشركة الناقلة:</span>
                    <span><?= htmlspecialchars($container['carrier'] ?: 'غير محدد') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الباخرة:</span>
                    <span><?= htmlspecialchars($container['ship_name'] ?: 'غير محدد') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">المحطة الجمركية:</span>
                    <span><?= htmlspecialchars($container['custom_station'] ?: 'غير محدد') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الوصول المتوقع:</span>
                    <span><?= htmlspecialchars($container['expected_arrival'] ?: 'غير محدد') ?></span>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="col-md-6">
            <div class="details-card">
                <h5 class="mb-4"><i class="bi bi-flag"></i> الحالة الحالية</h5>
                <div class="detail-item">
                    <span class="detail-label">حالة البوليصة:</span>
                    <span class="status-badge <?= getStatusClass($container['bill_of_lading_status']) ?>">
                        <?= getStatusText('bill_of_lading', $container['bill_of_lading_status']) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">حالة التختيم:</span>
                    <span class="status-badge <?= getStatusClass($container['tashitim_status']) ?>">
                        <?= getStatusText('tashitim', $container['tashitim_status']) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">الموقع الحالي:</span>
                    <span class="status-badge status-pending">
                        <?= translateStatus(getLatestPosition($position_history)) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">حالة الإفراج:</span>
                    <span class="status-badge <?= $container['release_status'] === 'Yes' ? 'status-completed' : 'status-pending' ?>">
                        <?= $container['release_status'] === 'Yes' ? 'تم الإفراج' : 'لم يتم الإفراج' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <?php if (!empty($container['notes'])): ?>
    <div class="details-card">
        <h5 class="mb-3"><i class="bi bi-journal-text"></i> ملاحظات</h5>
        <p class="mb-0"><?= nl2br(htmlspecialchars($container['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-custom" onclick="showUpdateModal()">
            <i class="bi bi-arrow-repeat"></i> تحديث الحالة
        </button>
        <button class="btn btn-custom" onclick="printStatus()">
            <i class="bi bi-printer"></i> طباعة التقرير
        </button>
        <button class="btn btn-custom" onclick="exportPDF()">
            <i class="bi bi-file-pdf"></i> تصدير PDF
        </button>
        <a href="edit_container.php?id=<?= $container_id ?>" class="btn btn-custom">
            <i class="bi bi-pencil"></i> تعديل البيانات
        </a>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث حالة الحاوية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <input type="hidden" name="container_id" value="<?= $container_id ?>">
                    
                    <!-- Position Status -->
                    <div class="mb-4">
                        <h6>الموقع الحالي</h6>
                        <select name="position_status" class="form-select">
                            <option value="">-- اختر الموقع --</option>
                            <option value="Loaded">تم التحميل</option>
                            <option value="At Port">في الميناء</option>
                            <option value="At Sea">في البحر</option>
                            <option value="Arrived">وصلت</option>
                            <option value="Transported by Land">تم الشحن البري</option>
                            <option value="Delivered">تم التسليم</option>
                            <option value="Empty Returned">تم تسليم الفارغ</option>
                        </select>
                    </div>

                    <!-- Bill of Lading Status -->
                    <div class="mb-4">
                        <h6>حالة البوليصة</h6>
                        <select name="bill_of_lading_status" class="form-select" onchange="toggleBillFields()">
                            <option value="">-- لا تغيير --</option>
                            <option value="not_issued" <?= $container['bill_of_lading_status'] === 'not_issued' ? 'selected' : '' ?>>لم يتم الإصدار</option>
                            <option value="issued" <?= $container['bill_of_lading_status'] === 'issued' ? 'selected' : '' ?>>تم الإصدار</option>
                            <option value="delayed" <?= $container['bill_of_lading_status'] === 'delayed' ? 'selected' : '' ?>>متأخر</option>
                        </select>
                        <div id="billFileDiv" class="mt-3" style="display: none;">
                            <label class="form-label">رفع ملف البوليصة</label>
                            <input type="file" name="bill_file" class="form-control" accept=".pdf,.jpg,.png">
                        </div>
                    </div>

                    <!-- Tashitim Status -->
                    <div class="mb-4">
                        <h6>حالة التختيم</h6>
                        <select name="tashitim_status" class="form-select" onchange="toggleTashitimFields()">
                            <option value="">-- لا تغيير --</option>
                            <option value="not_done" <?= $container['tashitim_status'] === 'not_done' ? 'selected' : '' ?>>لم يتم التختيم</option>
                            <option value="done" <?= $container['tashitim_status'] === 'done' ? 'selected' : '' ?>>تم التختيم</option>
                            <option value="delayed" <?= $container['tashitim_status'] === 'delayed' ? 'selected' : '' ?>>متأخر</option>
                        </select>
                        <div id="tashitimDateDiv" class="mt-3" style="display: none;">
                            <label class="form-label">تاريخ التختيم</label>
                            <input type="date" name="customs_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-custom" onclick="submitUpdate()">
                    <i class="bi bi-check-circle"></i> حفظ التحديثات
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showUpdateModal() {
    const modal = new bootstrap.Modal(document.getElementById('updateModal'));
    modal.show();
}

function toggleBillFields() {
    const status = document.querySelector('[name="bill_of_lading_status"]').value;
    document.getElementById('billFileDiv').style.display = status === 'issued' ? 'block' : 'none';
}

function toggleTashitimFields() {
    const status = document.querySelector('[name="tashitim_status"]').value;