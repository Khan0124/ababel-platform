<?php
/**
 * Secure Configuration File
 * Uses environment variables for sensitive data
 */

// Load environment variables
require_once __DIR__ . '/env.php';

try {
    Env::load();
} catch (Exception $e) {
    die('Configuration Error: ' . $e->getMessage());
}

// Database Configuration
$host = Env::get('DB_HOST', '127.0.0.1');
$port = Env::get('DB_PORT', '3306');
$user = Env::get('DB_USERNAME', 'root');
$pass = Env::get('DB_PASSWORD', '');
$db   = Env::get('DB_DATABASE', 'labor');

// Create secure database connection
try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    
    if ($conn->connect_error) {
        // Log error without exposing sensitive information
        error_log('Database connection failed: ' . $conn->connect_error);
        
        // Show generic error to user
        if (Env::get('APP_ENV') === 'production') {
            die('عذراً، حدث خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.');
        } else {
            die('Database connection failed: ' . $conn->connect_error);
        }
    }
    
    // Set charset to UTF8MB4 for full Unicode support
    if (!$conn->set_charset("utf8mb4")) {
        error_log('Error loading character set utf8mb4: ' . $conn->error);
    }
    
    // Set SQL mode for better data integrity
    $conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    
    if (Env::get('APP_ENV') === 'production') {
        die('عذراً، حدث خطأ في النظام. يرجى المحاولة لاحقاً.');
    } else {
        die('Database error: ' . $e->getMessage());
    }
}

// Application Configuration
define('APP_ENV', Env::get('APP_ENV', 'production'));
define('APP_DEBUG', filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
define('APP_URL', Env::get('APP_URL', 'http://localhost'));
define('APP_KEY', Env::get('APP_KEY', ''));

// Security Configuration
define('ENCRYPTION_KEY', Env::get('ENCRYPTION_KEY', APP_KEY));
define('SESSION_LIFETIME', (int)Env::get('SESSION_LIFETIME', 120));
define('SESSION_SECURE_COOKIE', filter_var(Env::get('SESSION_SECURE_COOKIE', 'true'), FILTER_VALIDATE_BOOLEAN));

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/error.log');
}

// Timezone
date_default_timezone_set('Africa/Cairo');

// Security Headers
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Autoload security classes
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/session_manager.php';

// Initialize security manager
$security = new SecurityManager($conn);

// Validate APP_KEY
if (empty(APP_KEY)) {
    die('APP_KEY is not set. Please generate a secure key and add it to your .env file.');
}
?>