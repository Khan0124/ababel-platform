<?php
// نظام الإشعارات المتقدم لنظام إدارة المختبرات

class NotificationManager {
    
    private $conn;
    private $security;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
        $this->security = new SecurityManager($database_connection);
    }
    
    // إرسال إشعار عام
    public function sendNotification($user_id, $user_type, $title, $message, $type = 'info', $action_url = null, $lab_id = null) {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, user_type, lab_id, title, message, type, action_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssss", $user_id, $user_type, $lab_id, $title, $message, $type, $action_url);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // إرسال إشعار لجميع مستخدمي مختبر معين
    public function sendLabNotification($lab_id, $title, $message, $type = 'info', $action_url = null) {
        // إرسال للموظفين
        $stmt = $this->conn->prepare("SELECT id FROM lab_employees WHERE lab_id = ? AND status = 'active'");
        $stmt->bind_param("i", $lab_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($employee = $result->fetch_assoc()) {
            $this->sendNotification($employee['id'], 'lab_employee', $title, $message, $type, $action_url, $lab_id);
        }
        
        $stmt->close();
        return true;
    }
    
    // إرسال إشعار لجميع المشرفين
    public function sendAdminNotification($title, $message, $type = 'info', $action_url = null) {
        $stmt = $this->conn->prepare("SELECT id FROM admins");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($admin = $result->fetch_assoc()) {
            $this->sendNotification($admin['id'], 'admin', $title, $message, $type, $action_url);
        }
        
        $stmt->close();
        return true;
    }
    
    // جلب الإشعارات لمستخدم معين
    public function getUserNotifications($user_id, $user_type, $limit = 10, $unread_only = false) {
        $where_clause = "user_id = ? AND user_type = ?";
        $params = [$user_id, $user_type];
        $types = "is";
        
        if ($unread_only) {
            $where_clause .= " AND is_read = 0";
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE $where_clause ORDER BY created_at DESC LIMIT ?");
        $types .= "i";
        $params[] = $limit;
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $stmt->close();
        return $notifications;
    }
    
    // عدد الإشعارات غير المقروءة
    public function getUnreadCount($user_id, $user_type) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND user_type = ? AND is_read = 0");
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        
        return $count;
    }
    
    // تحديد إشعار كمقروء
    public function markAsRead($notification_id, $user_id, $user_type) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ? AND user_type = ?");
        $stmt->bind_param("iis", $notification_id, $user_id, $user_type);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // تحديد جميع الإشعارات كمقروءة
    public function markAllAsRead($user_id, $user_type) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND user_type = ? AND is_read = 0");
        $stmt->bind_param("is", $user_id, $user_type);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // حذف إشعار
    public function deleteNotification($notification_id, $user_id, $user_type) {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ? AND user_type = ?");
        $stmt->bind_param("iis", $notification_id, $user_id, $user_type);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // إشعارات خاصة بالمختبرات
    
    // إشعار بوصول نتيجة جديدة
    public function notifyNewResult($lab_id, $patient_name, $exam_name) {
        $title = "نتيجة فحص جديدة";
        $message = "تم إدخال نتيجة فحص $exam_name للمريض $patient_name";
        
        $this->sendLabNotification($lab_id, $title, $message, 'success', 'results_list.php');
    }
    
    // إشعار بمريض جديد
    public function notifyNewPatient($lab_id, $patient_name) {
        $title = "مريض جديد";
        $message = "تم تسجيل مريض جديد: $patient_name";
        
        $this->sendLabNotification($lab_id, $title, $message, 'info', 'patients_list.php');
    }
    
    // إشعار بنفاد المخزون
    public function notifyLowStock($lab_id, $item_name, $current_quantity) {
        $title = "تحذير مخزون منخفض";
        $message = "المادة $item_name وصلت إلى كمية منخفضة: $current_quantity";
        
        $this->sendLabNotification($lab_id, $title, $message, 'warning', 'stock_list.php');
    }
    
    // إشعار بانتهاء صلاحية المواد
    public function notifyExpiringItems($lab_id, $items) {
        $items_list = implode(', ', $items);
        $title = "تحذير انتهاء صلاحية";
        $message = "المواد التالية ستنتهي صلاحيتها قريباً: $items_list";
        
        $this->sendLabNotification($lab_id, $title, $message, 'warning', 'stock_list.php');
    }
    
    // إشعار بتحديث النظام
    public function notifySystemUpdate($update_details) {
        $title = "تحديث النظام";
        $message = "تم تحديث النظام: $update_details";
        
        $this->sendAdminNotification($title, $message, 'info');
    }
    
    // إشعار بنشاط مشبوه في الأمان
    public function notifySecurityAlert($event_details, $ip_address) {
        $title = "تحذير أمني";
        $message = "تم رصد نشاط مشبوه: $event_details من العنوان $ip_address";
        
        $this->sendAdminNotification($title, $message, 'danger');
    }
    
    // إشعار بدفعة جديدة
    public function notifyNewPayment($lab_id, $amount) {
        $title = "دفعة جديدة";
        $message = "تم استلام دفعة بقيمة $amount ريال";
        
        // إشعار للمختبر
        $this->sendLabNotification($lab_id, $title, $message, 'success', 'cashbox.php');
        
        // إشعار للإدارة
        $this->sendAdminNotification($title, "من المختبر #$lab_id: $message", 'success');
    }
    
    // تنظيف الإشعارات القديمة
    public function cleanupOldNotifications($days = 30) {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->bind_param("i", $days);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // إحصائيات الإشعارات
    public function getNotificationStats($user_id, $user_type) {
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            COUNT(CASE WHEN type = 'danger' THEN 1 END) as critical,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as today
            FROM notifications 
            WHERE user_id = ? AND user_type = ?");
        
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }
    
    // إرسال إشعار WhatsApp (يتطلب API خارجي)
    public function sendWhatsAppNotification($phone_number, $message, $lab_id = null) {
        // هذه الدالة تحتاج إلى تكامل مع WhatsApp Business API
        // يمكن استخدام خدمات مثل Twilio أو Green API
        
        // مثال على حفظ طلب الإرسال في قاعدة البيانات
        $stmt = $this->conn->prepare("INSERT INTO whatsapp_queue (phone_number, message, lab_id, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ssi", $phone_number, $message, $lab_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    // إرسال إشعار بريد إلكتروني (يتطلب SMTP)
    public function sendEmailNotification($email, $subject, $message, $lab_id = null) {
        // مثال على حفظ طلب الإرسال في قاعدة البيانات
        $stmt = $this->conn->prepare("INSERT INTO email_queue (email, subject, message, lab_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("sssi", $email, $subject, $message, $lab_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}

// دوال مساعدة لإنشاء الجداول المطلوبة
function createNotificationTables($conn) {
    
    // جدول الإشعارات الرئيسي
    $notifications_sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('admin', 'lab_employee') NOT NULL,
        lab_id INT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
        action_url VARCHAR(500) NULL,
        is_read BOOLEAN DEFAULT FALSE,
        read_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_type),
        INDEX idx_lab (lab_id),
        INDEX idx_unread (is_read),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // جدول طوابير WhatsApp
    $whatsapp_queue_sql = "CREATE TABLE IF NOT EXISTS whatsapp_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone_number VARCHAR(20) NOT NULL,
        message TEXT NOT NULL,
        lab_id INT NULL,
        status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        attempts INT DEFAULT 0,
        last_attempt DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // جدول طوابير البريد الإلكتروني
    $email_queue_sql = "CREATE TABLE IF NOT EXISTS email_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(500) NOT NULL,
        message TEXT NOT NULL,
        lab_id INT NULL,
        status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        attempts INT DEFAULT 0,
        last_attempt DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($notifications_sql);
    $conn->query($whatsapp_queue_sql);
    $conn->query($email_queue_sql);
}

// تشغيل إنشاء الجداول
if (isset($conn)) {
    createNotificationTables($conn);
}
?>