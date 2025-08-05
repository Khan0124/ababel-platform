<?php
/**
 * Simple Fix Labs Table Migration
 * Updates the labs table to support the new authentication system
 */

// Database connection
$host = "127.0.0.1";
$user = "labor";
$pass = "Khan@70990100";
$db   = "labor";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 بدء إصلاح جدول المعامل...\n\n";
    
    // Add missing columns to labs table
    echo "📝 إضافة الأعمدة المفقودة...\n";
    
    // Add password column
    $pdo->exec("
        ALTER TABLE labs 
        ADD COLUMN IF NOT EXISTS password VARCHAR(255) AFTER email
    ");
    
    // Add subscription columns
    $pdo->exec("
        ALTER TABLE labs 
        ADD COLUMN IF NOT EXISTS subscription_type VARCHAR(50) DEFAULT 'basic' AFTER status,
        ADD COLUMN IF NOT EXISTS subscription_end_date DATE AFTER subscription_type
    ");
    
    // Add updated_at column
    $pdo->exec("
        ALTER TABLE labs 
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ");
    
    // Rename phone_main to phone for consistency
    $pdo->exec("
        ALTER TABLE labs 
        CHANGE COLUMN phone_main phone VARCHAR(20)
    ");
    
    // Add indexes for better performance
    echo "📝 إضافة الفهارس...\n";
    $pdo->exec("
        ALTER TABLE labs 
        ADD INDEX IF NOT EXISTS idx_email (email),
        ADD INDEX IF NOT EXISTS idx_status (status)
    ");
    
    // Update existing lab with a password
    echo "📝 تحديث المعمل الموجود...\n";
    
    // Check if the lab already has a password
    $stmt = $pdo->prepare("SELECT password FROM labs WHERE email = ?");
    $stmt->execute(['hmadakhan686@gmail.com']);
    $lab = $stmt->fetch();
    
    if (!$lab || empty($lab['password'])) {
        // Hash password using password_hash
        $hashedPassword = password_hash('admin123', PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
        
        $stmt = $pdo->prepare("
            UPDATE labs 
            SET password = ?, subscription_type = 'premium', status = 1
            WHERE email = ?
        ");
        $stmt->execute([$hashedPassword, 'hmadakhan686@gmail.com']);
        
        echo "✅ تم تحديث كلمة مرور المعمل الموجود\n";
    }
    
    // Update the status field to use boolean values
    echo "📝 تحديث حقل الحالة...\n";
    $pdo->exec("
        UPDATE labs 
        SET status = CASE 
            WHEN status = 'active' THEN 1 
            WHEN status = 'inactive' THEN 0 
            ELSE 1 
        END
    ");
    
    // Change status column type to BOOLEAN
    $pdo->exec("
        ALTER TABLE labs 
        MODIFY COLUMN status BOOLEAN DEFAULT 1
    ");
    
    echo "✅ تم إصلاح جدول المعامل بنجاح!\n";
    echo "\n📋 ملخص الإصلاحات:\n";
    echo "- تم إضافة عمود كلمة المرور\n";
    echo "- تم إضافة أعمدة الاشتراك\n";
    echo "- تم تحديث حقل الحالة\n";
    echo "- تم إضافة الفهارس\n";
    echo "- تم تحديث المعمل الموجود\n";
    
    echo "\n🔐 بيانات تسجيل الدخول:\n";
    echo "- البريد الإلكتروني: hmadakhan686@gmail.com\n";
    echo "- كلمة المرور: admin123\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إصلاح جدول المعامل: " . $e->getMessage() . "\n";
    exit(1);
}
?> 