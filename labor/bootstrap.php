<?php
/**
 * Application Bootstrap
 * Initializes the application with proper configuration and security
 */
// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تحميل متغيرات البيئة يدويًا من ملف .env
$envPath = __DIR__ . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // تخطي التعليقات

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // إزالة علامات الاقتباس إن وجدت
        $value = trim($value, "\"'");

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}


// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Autoloader for the application
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize application configuration
\App\Config\App::init();

// Initialize database connection
try {
    $db = \App\Config\Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    if (\App\Config\App::isProduction()) {
        die('عذراً، حدث خطأ في النظام. يرجى المحاولة لاحقاً.');
    } else {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Initialize security manager
$security = new \App\Core\Security($pdo);

// Initialize services
$authService = new \App\Services\AuthService($pdo);

// Clean up expired sessions periodically
if (rand(1, 100) <= 5) { // 5% chance to run cleanup
    $authService->cleanupExpiredSessions();
    $security->cleanupSecurityLogs();
}

// Set default timezone
date_default_timezone_set(\App\Config\App::get('TIMEZONE'));

// Define global constants
define('APP_ROOT', __DIR__);
define('APP_URL', \App\Config\App::get('APP_URL'));
define('APP_ENV', \App\Config\App::get('APP_ENV'));

// Helper functions
function asset($path) {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function csrf_token() {
    global $security;
    return $security->generateCSRFToken();
}

function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function sanitize($input) {
    global $security;
    return $security->sanitizeInput($input);
}

function flash($key, $message = null) {
    if ($message === null) {
        $message = $_SESSION['flash'][$key] ?? '';
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    
    $_SESSION['flash'][$key] = $message;
}

function has_flash($key) {
    return isset($_SESSION['flash'][$key]);
}

function format_currency($amount) {
    return number_format($amount, 2) . ' ج.م';
}

function format_date($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'Y-m-d H:i') {
    return date($format, strtotime($datetime));
}

function is_authenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

function current_user() {
    if (!is_authenticated()) {
        return null;
    }
    
    global $authService;
    $userType = $_SESSION['user_type'] ?? 'lab';
    
    if (!$authService->validateSession($_SESSION['user_id'], $_SESSION['session_token'], $userType)) {
        // Session expired, logout
        session_destroy();
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'type' => $userType,
        'lab_id' => $_SESSION['lab_id'] ?? null
    ];
}

function require_auth() {
    if (!is_authenticated()) {
        redirect('login');
    }
    
    $user = current_user();
    if (!$user) {
        redirect('login');
    }
    
    return $user;
}

function require_lab_auth() {
    $user = require_auth();
    if ($user['type'] !== 'lab') {
        redirect('login');
    }
    return $user;
}

function require_employee_auth() {
    $user = require_auth();
    if ($user['type'] !== 'employee') {
        redirect('login');
    }
    return $user;
}

// Set up error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set up exception handler
set_exception_handler(function($exception) {
    if (\App\Config\App::isProduction()) {
        error_log($exception->getMessage());
        http_response_code(500);
        include APP_ROOT . '/errors/500.php';
    } else {
        echo '<h1>Error</h1>';
        echo '<p>' . $exception->getMessage() . '</p>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
    }
    exit;
}); 