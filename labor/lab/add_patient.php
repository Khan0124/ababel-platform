<?php
require_once '../bootstrap.php';

// Require authentication
$user = require_lab_auth();
$labId = $user['lab_id'];

// Initialize models
$patientModel = new \App\Models\Patient($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['_token']) || !$security->verifyCSRFToken($_POST['_token'])) {
            throw new Exception('رمز الأمان غير صحيح');
        }
        
        // Validate and sanitize input
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $gender = sanitize($_POST['gender'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $insurance_company = sanitize($_POST['insurance_company'] ?? '');
        $insurance_number = sanitize($_POST['insurance_number'] ?? '');
        
        // Validation
        if (empty($name)) {
            throw new Exception('اسم المريض مطلوب');
        }
        
        if (empty($phone)) {
            throw new Exception('رقم الهاتف مطلوب');
        }
        
        if (!empty($email) && !$security->validateEmail($email)) {
            throw new Exception('البريد الإلكتروني غير صحيح');
        }
        
        if ($age <= 0 || $age > 150) {
            throw new Exception('العمر يجب أن يكون بين 1 و 150');
        }
        
        if (empty($gender) || !in_array($gender, ['ذكر', 'أنثى'])) {
            throw new Exception('الجنس مطلوب');
        }
        
        // Check if phone number already exists
        $existingPatient = $patientModel->findBy('phone', $phone);
        if ($existingPatient && $existingPatient['lab_id'] == $labId) {
            throw new Exception('رقم الهاتف مسجل مسبقاً');
        }
        
        // Prepare patient data
        $patientData = [
            'lab_id' => $labId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'age' => $age,
            'gender' => $gender,
            'address' => $address,
            'insurance_company' => $insurance_company,
            'insurance_number' => $insurance_number
        ];
        
        // Create patient
        $patientId = $patientModel->create($patientData);
        
        if ($patientId) {
            $success = 'تم إضافة المريض بنجاح';
            $_SESSION['old'] = []; // Clear old input
        } else {
            throw new Exception('حدث خطأ أثناء إضافة المريض');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $_SESSION['old'] = $_POST;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مريض جديد - نظام إدارة المختبرات</title>
    
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
        
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            color: #991b1b;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #bbf7d0, #86efac);
            color: #166534;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-submit:hover::before {
            left: 100%;
        }
        
        .btn-cancel {
            background: #64748b;
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #475569;
            color: white;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        
        .required {
            color: #ef4444;
        }
        
        .help-text {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
                <h1 class="page-title">إضافة مريض جديد</h1>
                <p class="page-subtitle">إدخال بيانات المريض الجديد</p>
            </div>
            
            <!-- Form Container -->
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="patientForm">
                    <?= csrf_field() ?>
                    
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="bi bi-person-fill me-2"></i>
                            المعلومات الشخصية
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    اسم المريض <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= old('name') ?>"
                                       required>
                                <div class="help-text">أدخل الاسم الكامل للمريض</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    رقم الهاتف <span class="required">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= old('phone') ?>"
                                       required>
                                <div class="help-text">أدخل رقم الهاتف مع رمز الدولة</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= old('email') ?>">
                                <div class="help-text">اختياري - للتواصل الإلكتروني</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="age" class="form-label">
                                    العمر <span class="required">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="age" 
                                       name="age" 
                                       min="1" 
                                       max="150" 
                                       value="<?= old('age') ?>"
                                       required>
                                <div class="help-text">أدخل العمر بالسنوات</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender" class="form-label">
                                    الجنس <span class="required">*</span>
                                </label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">اختر الجنس</option>
                                    <option value="ذكر" <?= old('gender') === 'ذكر' ? 'selected' : '' ?>>ذكر</option>
                                    <option value="أنثى" <?= old('gender') === 'أنثى' ? 'selected' : '' ?>>أنثى</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">العنوان</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="3"><?= old('address') ?></textarea>
                                <div class="help-text">اختياري - عنوان المريض</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Insurance Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="bi bi-shield-check me-2"></i>
                            معلومات التأمين
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="insurance_company" class="form-label">شركة التأمين</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="insurance_company" 
                                       name="insurance_company" 
                                       value="<?= old('insurance_company') ?>">
                                <div class="help-text">اختياري - اسم شركة التأمين</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="insurance_number" class="form-label">رقم التأمين</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="insurance_number" 
                                       name="insurance_number" 
                                       value="<?= old('insurance_number') ?>">
                                <div class="help-text">اختياري - رقم بطاقة التأمين</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-check-circle me-2"></i>
                            حفظ المريض
                        </button>
                        <a href="patients_list.php" class="btn btn-cancel">
                            <i class="bi bi-x-circle me-2"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.getElementById('patientForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const age = document.getElementById('age').value;
            const gender = document.getElementById('gender').value;
            
            if (!name) {
                e.preventDefault();
                alert('يرجى إدخال اسم المريض');
                document.getElementById('name').focus();
                return false;
            }
            
            if (!phone) {
                e.preventDefault();
                alert('يرجى إدخال رقم الهاتف');
                document.getElementById('phone').focus();
                return false;
            }
            
            if (!age || age < 1 || age > 150) {
                e.preventDefault();
                alert('يرجى إدخال عمر صحيح (1-150)');
                document.getElementById('age').focus();
                return false;
            }
            
            if (!gender) {
                e.preventDefault();
                alert('يرجى اختيار الجنس');
                document.getElementById('gender').focus();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>جاري الحفظ...';
            submitBtn.disabled = true;
        });
        
        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        
        // Auto-focus on name field
        document.getElementById('name').focus();
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = '+' + value;
                } else if (value.length <= 6) {
                    value = '+' + value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    value = '+' + value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
