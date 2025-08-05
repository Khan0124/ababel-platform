<?php
require_once '../bootstrap.php';

// Require authentication
$user = require_lab_auth();
$labId = $user['lab_id'];

// Initialize models
$patientModel = new \App\Models\Patient($pdo);

// Handle search
$search = sanitize($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Get patients with pagination
if (!empty($search)) {
    $patients = $patientModel->searchPatients($labId, $search);
    $totalPatients = count($patients);
    $patients = array_slice($patients, ($page - 1) * $perPage, $perPage);
} else {
    $pagination = $patientModel->paginate($page, $perPage, ['lab_id' => $labId], ['name' => 'ASC']);
    $patients = $pagination['data'];
    $totalPatients = $pagination['total'];
    $lastPage = $pagination['last_page'];
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة المرضى - نظام إدارة المختبرات</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/modern-style.css" rel="stylesheet">
    
    <style>
        .page-container {
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
        
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-search {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }
        
        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .patient-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .patient-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .patient-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .patient-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .patient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-left: 15px;
        }
        
        .patient-info {
            flex: 1;
        }
        
        .patient-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .patient-details {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .patient-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-action {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-view {
            background: #667eea;
            color: white;
        }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
        }
        
        .btn-exams {
            background: #10b981;
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .pagination-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .pagination {
            justify-content: center;
        }
        
        .page-link {
            border: none;
            color: #667eea;
            padding: 10px 15px;
            margin: 0 2px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: #667eea;
            color: white;
        }
        
        .page-item.active .page-link {
            background: #667eea;
            color: white;
        }
        
        .no-results {
            text-align: center;
            padding: 50px 20px;
            color: #64748b;
        }
        
        .no-results i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
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
            
            .search-form {
                flex-direction: column;
            }
            
            .patients-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="lab-logo">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <div class="lab-name">نظام إدارة المختبرات</div>
                <div class="lab-subscription">النسخة المطورة</div>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i>
                        لوحة التحكم
                    </a>
                </div>
                <div class="nav-item">
                    <a href="patients_list.php" class="nav-link active">
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
                <h1 class="page-title">قائمة المرضى</h1>
                <p class="page-subtitle">إدارة بيانات المرضى والفحوصات</p>
            </div>
            
            <!-- Search Section -->
            <div class="search-section">
                <form method="GET" class="search-form">
                    <div class="form-group">
                        <label for="search" class="form-label">البحث في المرضى</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="اسم المريض، رقم الهاتف، أو رقم التأمين"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-search">
                            <i class="bi bi-search me-2"></i>
                            بحث
                        </button>
                    </div>
                    <div class="form-group">
                        <a href="add_patient.php" class="btn btn-add">
                            <i class="bi bi-plus-circle me-2"></i>
                            إضافة مريض جديد
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Patients Grid -->
            <?php if (empty($patients)): ?>
                <div class="no-results">
                    <i class="bi bi-people"></i>
                    <h3>لا توجد نتائج</h3>
                    <p><?= empty($search) ? 'لا يوجد مرضى مسجلين حالياً' : 'لم يتم العثور على مرضى مطابقين للبحث' ?></p>
                    <?php if (!empty($search)): ?>
                        <a href="patients_list.php" class="btn btn-search mt-3">عرض جميع المرضى</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="patients-grid">
                    <?php foreach ($patients as $patient): ?>
                        <div class="patient-card">
                            <div class="patient-header">
                                <div class="patient-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="patient-info">
                                    <div class="patient-name"><?= htmlspecialchars($patient['name']) ?></div>
                                    <div class="patient-details">
                                        <?= htmlspecialchars($patient['phone']) ?> • 
                                        <?= $patient['age'] ?> سنة • 
                                        <?= $patient['gender'] ?>
                                        <?php if (!empty($patient['insurance_company'])): ?>
                                            <br><small>تأمين: <?= htmlspecialchars($patient['insurance_company']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="patient-actions">
                                <a href="view_patient.php?id=<?= $patient['id'] ?>" class="btn-action btn-view">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض
                                </a>
                                <a href="edit_patient.php?id=<?= $patient['id'] ?>" class="btn-action btn-edit">
                                    <i class="bi bi-pencil me-1"></i>
                                    تعديل
                                </a>
                                <a href="patient_exams.php?patient_id=<?= $patient['id'] ?>" class="btn-action btn-exams">
                                    <i class="bi bi-clipboard2-pulse me-1"></i>
                                    الفحوصات
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($lastPage) && $lastPage > 1): ?>
                    <div class="pagination-container">
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $lastPage): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus on search field
        document.getElementById('search').focus();
        
        // Real-time search (optional)
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
    </script>
</body>
</html>
