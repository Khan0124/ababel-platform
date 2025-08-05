<?php
/**
 * Database Setup Script
 * Creates all necessary tables for the Labor Management System
 */

require_once '../bootstrap.php';

try {
    echo "🚀 بدء إعداد قاعدة البيانات...\n\n";
    
    // Create security_logs table
    echo "📝 إنشاء جدول سجلات الأمان...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS security_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type),
            INDEX idx_ip_address (ip_address),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create lab_sessions table
    echo "📝 إنشاء جدول جلسات المعامل...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lab_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lab_id (lab_id),
            INDEX idx_session_token (session_token),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create employee_sessions table
    echo "📝 إنشاء جدول جلسات الموظفين...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee_id (employee_id),
            INDEX idx_session_token (session_token),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Update labs table if it exists
    echo "📝 تحديث جدول المعامل...\n";
    $pdo->exec("
        ALTER TABLE labs 
        ADD COLUMN IF NOT EXISTS subscription_type VARCHAR(50) DEFAULT 'basic',
        ADD COLUMN IF NOT EXISTS subscription_end_date DATE,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD INDEX IF NOT EXISTS idx_email (email),
        ADD INDEX IF NOT EXISTS idx_status (status)
    ");
    
    // Update patients table if it exists
    echo "📝 تحديث جدول المرضى...\n";
    $pdo->exec("
        ALTER TABLE patients 
        ADD COLUMN IF NOT EXISTS email VARCHAR(255),
        ADD COLUMN IF NOT EXISTS insurance_company VARCHAR(255),
        ADD COLUMN IF NOT EXISTS insurance_number VARCHAR(255),
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD INDEX IF NOT EXISTS idx_lab_id (lab_id),
        ADD INDEX IF NOT EXISTS idx_phone (phone),
        ADD INDEX IF NOT EXISTS idx_name (name)
    ");
    
    // Update lab_employees table if it exists
    echo "📝 تحديث جدول موظفي المعامل...\n";
    $pdo->exec("
        ALTER TABLE lab_employees 
        ADD COLUMN IF NOT EXISTS email VARCHAR(255),
        ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'فني',
        ADD COLUMN IF NOT EXISTS status BOOLEAN DEFAULT 1,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD INDEX IF NOT EXISTS idx_lab_id (lab_id),
        ADD INDEX IF NOT EXISTS idx_username (username),
        ADD INDEX IF NOT EXISTS idx_status (status)
    ");
    
    // Update patient_exams table if it exists
    echo "📝 تحديث جدول فحوصات المرضى...\n";
    $pdo->exec("
        ALTER TABLE patient_exams 
        ADD COLUMN IF NOT EXISTS employee_id INT,
        ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS notes TEXT,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD INDEX IF NOT EXISTS idx_lab_id (lab_id),
        ADD INDEX IF NOT EXISTS idx_patient_id (patient_id),
        ADD INDEX IF NOT EXISTS idx_status (status),
        ADD INDEX IF NOT EXISTS idx_created_at (created_at)
    ");
    
    // Create exam_catalog table if it doesn't exist
    echo "📝 إنشاء جدول كتالوج الفحوصات...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS exam_catalog (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lab_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) DEFAULT 0.00,
            category VARCHAR(100),
            status BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lab_id (lab_id),
            INDEX idx_category (category),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create cashbox table if it doesn't exist
    echo "📝 إنشاء جدول الصندوق...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cashbox (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lab_id INT NOT NULL,
            patient_id INT,
            exam_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_type ENUM('نقدي', 'بطاقة', 'تحويل بنكي') DEFAULT 'نقدي',
            status ENUM('مدفوع', 'غير مدفوع', 'مسترد') DEFAULT 'مدفوع',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lab_id (lab_id),
            INDEX idx_patient_id (patient_id),
            INDEX idx_exam_id (exam_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create employee_attendance table if it doesn't exist
    echo "📝 إنشاء جدول حضور الموظفين...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            date DATE NOT NULL,
            check_in_time TIME,
            check_out_time TIME,
            total_hours DECIMAL(4,2),
            status ENUM('حاضر', 'غائب', 'إجازة') DEFAULT 'حاضر',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_employee_date (employee_id, date),
            INDEX idx_employee_id (employee_id),
            INDEX idx_date (date),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create insurance_companies table if it doesn't exist
    echo "📝 إنشاء جدول شركات التأمين...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS insurance_companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lab_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255),
            phone VARCHAR(50),
            email VARCHAR(255),
            address TEXT,
            contract_number VARCHAR(100),
            contract_start_date DATE,
            contract_end_date DATE,
            discount_percentage DECIMAL(5,2) DEFAULT 0.00,
            status BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lab_id (lab_id),
            INDEX idx_name (name),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert sample data for testing
    echo "📝 إدخال بيانات تجريبية...\n";
    
    // Insert sample lab if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM labs WHERE email = ?");
    $stmt->execute(['admin@labor.com']);
    if ($stmt->fetchColumn() == 0) {
        $security = new \App\Core\Security($pdo);
        $hashedPassword = $security->hashPassword('admin123');
        
        $stmt = $pdo->prepare("
            INSERT INTO labs (name, email, phone, address, password, subscription_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'معمل تجريبي',
            'admin@labor.com',
            '+201234567890',
            'القاهرة، مصر',
            $hashedPassword,
            'premium',
            1
        ]);
        echo "✅ تم إنشاء معمل تجريبي\n";
    }
    
    // Insert sample exam categories
    $categories = [
        'فحوصات الدم',
        'فحوصات البول',
        'فحوصات الكيمياء',
        'فحوصات المناعة',
        'فحوصات الهرمونات'
    ];
    
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM exam_catalog WHERE name = ? AND lab_id = 1");
        $stmt->execute([$category]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO exam_catalog (lab_id, name, description, price, category) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                1,
                $category,
                'وصف تفصيلي للفحص',
                rand(50, 500),
                $category
            ]);
        }
    }
    
    echo "✅ تم إدخال بيانات تجريبية\n";
    
    echo "\n🎉 تم إعداد قاعدة البيانات بنجاح!\n";
    echo "\n📋 ملخص الإعدادات:\n";
    echo "- تم إنشاء جميع الجداول المطلوبة\n";
    echo "- تم إضافة الفهارس لتحسين الأداء\n";
    echo "- تم إعداد البيانات التجريبية\n";
    echo "- تم تطبيق أفضل ممارسات الأمان\n";
    
    echo "\n🔐 بيانات تسجيل الدخول التجريبية:\n";
    echo "- البريد الإلكتروني: admin@labor.com\n";
    echo "- كلمة المرور: admin123\n";
    
    echo "\n⚠️  تحذير: يرجى تغيير كلمة المرور في الإنتاج!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إعداد قاعدة البيانات: " . $e->getMessage() . "\n";
    exit(1);
}
?> 