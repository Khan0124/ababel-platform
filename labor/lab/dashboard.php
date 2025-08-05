<?php
require_once '../bootstrap.php';

// Require authentication
$user = require_lab_auth();
$labId = $user['lab_id'];

// Initialize models
$labModel = new \App\Models\Lab($pdo);
$patientModel = new \App\Models\Patient($pdo);
$examModel = new \App\Models\Exam($pdo);
$employeeModel = new \App\Models\LabEmployee($pdo);

// Get lab information
$lab = $labModel->find($labId);
$labStats = $labModel->getLabStats($labId);

// Get recent data
$recentPatients = $patientModel->getRecentPatients($labId, 5);
$recentExams = $examModel->getRecentExams($labId, 5);
$pendingExams = $examModel->getPendingExams($labId, 10);

// Get employee stats
$employeeStats = $employeeModel->getEmployeeStats($labId);

// Get patient statistics
$patientStats = $patientModel->getPatientStats($labId);
$patientsByAge = $patientModel->getPatientsByAgeGroup($labId);
$patientsByInsurance = $patientModel->getPatientsByInsurance($labId);

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?= htmlspecialchars($lab['name']) ?></title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="../assets/modern-style.css" rel="stylesheet">
    
    <style>
        .dashboard-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .lab-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
        
        .lab-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .lab-subscription {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .nav-menu {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin-right: 15px;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(-5px);
        }
        
        .nav-link i {
            margin-left: 12px;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-right: 280px;
            padding: 30px;
        }
        
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }
        
        .recent-list {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .list-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .list-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        .list-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            color: white;
            font-size: 1rem;
        }
        
        .list-item-content {
            flex: 1;
        }
        
        .list-item-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }
        
        .list-item-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .list-item-time {
            color: #94a3b8;
            font-size: 0.8rem;
        }
        
        .btn-view-all {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            color: white;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-view-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="lab-logo">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <div class="lab-name"><?= htmlspecialchars($lab['name']) ?></div>
                <div class="lab-subscription"><?= htmlspecialchars($lab['subscription_type']) ?></div>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i>
                        لوحة التحكم
                    </a>
                </div>
                <div class="nav-item">
                    <a href="patients_list.php" class="nav-link">
                        <i class="bi bi-people-fill"></i>
                        المرضى
                    </a>
                </div>
                <div class="nav-item">
                    <a href="exams_list.php" class="nav-link">
                        <i class="bi bi-clipboard2-pulse-fill"></i>
                        الفحوصات
                    </a>
                </div>
                <div class="nav-item">
                    <a href="results_list.php" class="nav-link">
                        <i class="bi bi-file-earmark-text-fill"></i>
                        النتائج
                    </a>
                </div>
                <div class="nav-item">
                    <a href="employees_list.php" class="nav-link">
                        <i class="bi bi-person-badge-fill"></i>
                        الموظفين
                    </a>
                </div>
                <div class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="bi bi-graph-up"></i>
                        التقارير
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="bi bi-gear-fill"></i>
                        الإعدادات
                    </a>
                </div>
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="bi bi-box-arrow-right"></i>
                        تسجيل الخروج
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">مرحباً، <?= htmlspecialchars($user['name']) ?></h1>
                <p class="page-subtitle">نظرة عامة على نشاط المعمل</p>
            </div>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-number"><?= number_format($labStats['total_patients'] ?? 0) ?></div>
                    <div class="stat-label">إجمالي المرضى</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="bi bi-clipboard2-pulse-fill"></i>
                    </div>
                    <div class="stat-number"><?= number_format($labStats['total_exams'] ?? 0) ?></div>
                    <div class="stat-label">إجمالي الفحوصات</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div class="stat-number"><?= number_format($labStats['total_results'] ?? 0) ?></div>
                    <div class="stat-label">النتائج المكتملة</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="stat-number"><?= number_format($labStats['unsubmitted_count'] ?? 0) ?></div>
                    <div class="stat-label">في انتظار التسليم</div>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Charts -->
                <div class="chart-card">
                    <h3 class="chart-title">إحصائيات المرضى</h3>
                    <canvas id="patientsChart" width="400" height="200"></canvas>
                </div>
                
                <!-- Recent Activity -->
                <div class="recent-list">
                    <div class="list-title">
                        النشاط الأخير
                        <a href="recent_activity.php" class="btn-view-all">عرض الكل</a>
                    </div>
                    
                    <?php foreach ($recentPatients as $patient): ?>
                    <div class="list-item">
                        <div class="list-item-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="list-item-content">
                            <div class="list-item-title"><?= htmlspecialchars($patient['name']) ?></div>
                            <div class="list-item-subtitle"><?= htmlspecialchars($patient['phone']) ?></div>
                        </div>
                        <div class="list-item-time"><?= format_date($patient['created_at']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Patients Chart
        const ctx = document.getElementById('patientsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ذكر', 'أنثى'],
                datasets: [{
                    data: [
                        <?= $patientStats['male_count'] ?? 0 ?>,
                        <?= $patientStats['female_count'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#f093fb'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
        
        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html> 