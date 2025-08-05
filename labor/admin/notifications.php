<?php
session_start();
include '../includes/auth.php';
include '../includes/config.php';
include '../includes/notifications.php';

$notificationManager = new NotificationManager($conn);

// معالجة العمليات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                if (isset($_POST['notification_id'])) {
                    $notificationManager->markAsRead($_POST['notification_id'], $_SESSION['admin_id'], 'admin');
                }
                break;
                
            case 'mark_all_read':
                $notificationManager->markAllAsRead($_SESSION['admin_id'], 'admin');
                break;
                
            case 'delete':
                if (isset($_POST['notification_id'])) {
                    $notificationManager->deleteNotification($_POST['notification_id'], $_SESSION['admin_id'], 'admin');
                }
                break;
        }
        
        header('Location: notifications.php');
        exit;
    }
}

// جلب الإشعارات
$all_notifications = $notificationManager->getUserNotifications($_SESSION['admin_id'], 'admin', 50);
$unread_notifications = $notificationManager->getUserNotifications($_SESSION['admin_id'], 'admin', 10, true);
$stats = $notificationManager->getNotificationStats($_SESSION['admin_id'], 'admin');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإشعارات - نظام إدارة المختبرات الطبية</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/modern-style.css" rel="stylesheet">
    
    <style>
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .notification-item.unread {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(255, 255, 255, 0.95));
            border-left-color: var(--primary-color);
        }
        
        .notification-item:hover {
            background: rgba(37, 99, 235, 0.05);
            transform: translateX(-5px);
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
        }
        
        .notification-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .notification-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }
        
        .notification-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .notification-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .notification-time {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .notification-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .notification-item:hover .notification-actions {
            opacity: 1;
        }
        
        .stats-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .filter-tabs .nav-link {
            border-radius: 10px;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .filter-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <!-- النافذة الجانبية (نفس التصميم من dashboard.php) -->
    <nav class="sidebar" style="position: fixed; top: 0; right: 0; height: 100vh; width: 280px; background: linear-gradient(135deg, #2563eb, #1e40af); box-shadow: -5px 0 15px rgba(0,0,0,0.1); z-index: 1000; overflow-y: auto;">
        <div style="padding: 2rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <a href="dashboard.php" style="color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-microscope" style="margin-left: 0.5rem; font-size: 2rem;"></i>
                نظام المختبرات
            </a>
        </div>
        
        <div style="padding: 1rem 0;">
            <div style="margin: 0.25rem 1rem;">
                <a href="dashboard.php" style="color: rgba(255,255,255,0.8); padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease; display: flex; align-items: center; font-weight: 500; text-decoration: none;">
                    <i class="fas fa-chart-pie" style="margin-left: 0.75rem; width: 20px; text-align: center;"></i>
                    لوحة التحكم
                </a>
            </div>
            
            <div style="margin: 0.25rem 1rem;">
                <a href="labs_list.php" style="color: rgba(255,255,255,0.8); padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease; display: flex; align-items: center; font-weight: 500; text-decoration: none;">
                    <i class="fas fa-hospital" style="margin-left: 0.75rem; width: 20px; text-align: center;"></i>
                    إدارة المختبرات
                </a>
            </div>
            
            <div style="margin: 0.25rem 1rem;">
                <a href="notifications.php" style="background: rgba(255,255,255,0.1); color: white; padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease; display: flex; align-items: center; font-weight: 500; text-decoration: none;">
                    <i class="fas fa-bell" style="margin-left: 0.75rem; width: 20px; text-align: center;"></i>
                    الإشعارات
                    <?php if($stats['unread'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $stats['unread'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <hr style="margin: 1rem; border-color: rgba(255,255,255,0.2);">
            
            <div style="margin: 0.25rem 1rem;">
                <a href="logout.php" style="color: #f87171; padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease; display: flex; align-items: center; font-weight: 500; text-decoration: none;">
                    <i class="fas fa-sign-out-alt" style="margin-left: 0.75rem; width: 20px; text-align: center;"></i>
                    تسجيل الخروج
                </a>
            </div>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <main style="margin-right: 280px; min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <!-- شريط التنقل العلوي -->
        <nav style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-bottom: 1px solid rgba(0,0,0,0.1);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">الإشعارات</h5>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="ms-2"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'المشرف') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- المحتوى -->
        <div style="padding: 2rem;">
            <!-- إحصائيات الإشعارات -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number text-primary"><?= $stats['total'] ?></div>
                        <div class="stats-label">إجمالي الإشعارات</div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number text-warning"><?= $stats['unread'] ?></div>
                        <div class="stats-label">غير مقروءة</div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number text-danger"><?= $stats['critical'] ?></div>
                        <div class="stats-label">مهمة</div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number text-info"><?= $stats['today'] ?></div>
                        <div class="stats-label">اليوم</div>
                    </div>
                </div>
            </div>

            <!-- أدوات الإدارة -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">إدارة الإشعارات</h6>
                        
                        <div>
                            <?php if($stats['unread'] > 0): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="mark_all_read">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check-double me-1"></i>
                                        تحديد الكل كمقروء
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة الإشعارات -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs filter-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all-notifications">
                                جميع الإشعارات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#unread-notifications">
                                غير المقروءة (<?= $stats['unread'] ?>)
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <!-- جميع الإشعارات -->
                        <div class="tab-pane fade show active" id="all-notifications">
                            <?php if (empty($all_notifications)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                    <h5>لا توجد إشعارات</h5>
                                    <p>ستظهر الإشعارات الجديدة هنا</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($all_notifications as $notification): ?>
                                    <div class="notification-item p-3 border-bottom <?= !$notification['is_read'] ? 'unread' : '' ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon <?= $notification['type'] ?>">
                                                <i class="fas fa-<?= 
                                                    $notification['type'] === 'success' ? 'check-circle' : 
                                                    ($notification['type'] === 'warning' ? 'exclamation-triangle' : 
                                                    ($notification['type'] === 'danger' ? 'exclamation-circle' : 'info-circle'))
                                                ?>"></i>
                                            </div>
                                            
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 <?= !$notification['is_read'] ? 'fw-bold' : '' ?>">
                                                            <?= htmlspecialchars($notification['title']) ?>
                                                        </h6>
                                                        <p class="mb-1 text-muted">
                                                            <?= htmlspecialchars($notification['message']) ?>
                                                        </p>
                                                        <small class="notification-time">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= date('Y/m/d H:i', strtotime($notification['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="notification-actions">
                                                        <?php if (!$notification['is_read']): ?>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="action" value="mark_read">
                                                                <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="تحديد كمقروء">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($notification['action_url']): ?>
                                                            <a href="<?= htmlspecialchars($notification['action_url']) ?>" class="btn btn-sm btn-outline-success me-1" title="عرض">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا الإشعار؟')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- الإشعارات غير المقروءة -->
                        <div class="tab-pane fade" id="unread-notifications">
                            <?php if (empty($unread_notifications)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h5>رائع! قرأت جميع الإشعارات</h5>
                                    <p>لا توجد إشعارات غير مقروءة</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($unread_notifications as $notification): ?>
                                    <div class="notification-item unread p-3 border-bottom">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon <?= $notification['type'] ?>">
                                                <i class="fas fa-<?= 
                                                    $notification['type'] === 'success' ? 'check-circle' : 
                                                    ($notification['type'] === 'warning' ? 'exclamation-triangle' : 
                                                    ($notification['type'] === 'danger' ? 'exclamation-circle' : 'info-circle'))
                                                ?>"></i>
                                            </div>
                                            
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">
                                                            <?= htmlspecialchars($notification['title']) ?>
                                                        </h6>
                                                        <p class="mb-1 text-muted">
                                                            <?= htmlspecialchars($notification['message']) ?>
                                                        </p>
                                                        <small class="notification-time">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= date('Y/m/d H:i', strtotime($notification['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="notification-actions" style="opacity: 1;">
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="تحديد كمقروء">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <?php if ($notification['action_url']): ?>
                                                            <a href="<?= htmlspecialchars($notification['action_url']) ?>" class="btn btn-sm btn-outline-success me-1" title="عرض">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // تحديث تلقائي كل دقيقة
        setTimeout(() => {
            location.reload();
        }, 60000);
        
        // إضافة تأثيرات تفاعلية
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>