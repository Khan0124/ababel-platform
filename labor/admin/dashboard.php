<?php
include 'auth_check.php';
include '../includes/config.php';

// استعلامات محسنة للإحصائيات
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM labs) as total_labs,
    (SELECT COUNT(*) FROM labs WHERE status = 'active') as active_labs,
    (SELECT COUNT(*) FROM tickets WHERE status = 'open') as open_tickets,
    (SELECT COUNT(*) FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as weekly_tickets,
    (SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as daily_activities,
    (SELECT COUNT(*) FROM subscriptions WHERE status = 'active') as active_subscriptions";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// أحدث الأنشطة
$recent_activities = $conn->query("SELECT al.*, COALESCE(l.name, 'إدارة النظام') as lab_name 
                                  FROM activity_logs al 
                                  LEFT JOIN labs l ON al.lab_id = l.id 
                                  ORDER BY al.created_at DESC LIMIT 5");

// إحصائيات الأداء
$performance_query = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as activities_count
    FROM activity_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC";
$performance_data = $conn->query($performance_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الرئيسية - نظام إدارة المختبرات الطبية</title>
    
    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/modern-style.css" rel="stylesheet">
    
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand i {
            margin-left: 0.5rem;
            font-size: 2rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(-5px);
        }
        
        .nav-link i {
            margin-left: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-right: 280px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .top-navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .content-wrapper {
            padding: 2rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .activity-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            font-size: 0.875rem;
        }
        
        .activity-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .activity-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }
        
        .activity-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-microscope"></i>
                نظام المختبرات
            </a>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-chart-pie"></i>
                    لوحة التحكم
                </a>
            </div>
            
            <div class="nav-item">
                <a href="labs_list.php" class="nav-link">
                    <i class="fas fa-hospital"></i>
                    إدارة المختبرات
                </a>
            </div>
            
            <div class="nav-item">
                <a href="subscriptions_list.php" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    الاشتراكات
                </a>
            </div>
            
            <div class="nav-item">
                <a href="tickets_list.php" class="nav-link">
                    <i class="fas fa-ticket-alt"></i>
                    التذاكر
                    <?php if($stats['open_tickets'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $stats['open_tickets'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="activity_logs.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    سجل الأنشطة
                </a>
            </div>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            
            <div class="nav-item">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    تسجيل الخروج
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-link mobile-toggle d-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0">لوحة التحكم الرئيسية</h5>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="ms-2"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'المشرف') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Welcome Card -->
            <div class="welcome-card fade-in-up">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">مرحباً بعودتك، <?= htmlspecialchars($_SESSION['admin_name'] ?? 'المشرف') ?>! 👋</h2>
                        <p class="mb-0 opacity-90">إليك نظرة سريعة على أداء النظام اليوم</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="fs-4 opacity-75">
                            <i class="fas fa-chart-line me-2"></i>
                            <?= date('Y/m/d') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stats-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="stats-number"><?= number_format($stats['total_labs']) ?></div>
                    <div class="stats-label">إجمالي المختبرات</div>
                    <div class="icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>

                <div class="stats-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="stats-number"><?= number_format($stats['active_labs']) ?></div>
                    <div class="stats-label">المختبرات النشطة</div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>

                <div class="stats-card fade-in-up" style="animation-delay: 0.3s;">
                    <div class="stats-number"><?= number_format($stats['active_subscriptions']) ?></div>
                    <div class="stats-label">الاشتراكات النشطة</div>
                    <div class="icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                </div>

                <div class="stats-card fade-in-up" style="animation-delay: 0.4s;">
                    <div class="stats-number"><?= number_format($stats['open_tickets']) ?></div>
                    <div class="stats-label">التذاكر المفتوحة</div>
                    <div class="icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="activity-card fade-in-up" style="animation-delay: 0.5s;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">أحدث الأنشطة</h5>
                            <a href="activity_logs.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                        
                        <?php if ($recent_activities->num_rows > 0): ?>
                            <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon info">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars($activity['description']) ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($activity['lab_name']) ?> • 
                                            <?= date('Y/m/d H:i', strtotime($activity['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <p>لا توجد أنشطة حديثة</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="activity-card fade-in-up" style="animation-delay: 0.6s;">
                        <h5 class="section-title mb-3">إحصائيات سريعة</h5>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>النشاط اليومي</span>
                            <span class="badge bg-primary"><?= $stats['daily_activities'] ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>تذاكر هذا الأسبوع</span>
                            <span class="badge bg-info"><?= $stats['weekly_tickets'] ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span>معدل النشاط</span>
                            <span class="text-success fw-bold">
                                <?= $stats['daily_activities'] > 10 ? 'مرتفع' : ($stats['daily_activities'] > 5 ? 'متوسط' : 'منخفض') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>