<?php
/**
 * Application Bootstrap
 * Modern application initialization with security and error handling
 */

// Start output buffering
ob_start();

// Set error reporting based on environment
$environment = $_ENV['APP_ENV'] ?? 'production';

if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Shanghai');

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Import core classes
use App\Core\Router;
use App\Core\SecurityManager;
use App\Core\Database;
use App\Core\ErrorHandlingMiddleware;

// Initialize security manager
$security = SecurityManager::getInstance();

// Initialize error handling middleware
$errorMiddleware = new ErrorHandlingMiddleware();

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    // Initialize router
    $router = new Router();
    
    // Get request method and URI
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Handle PUT and DELETE requests (for forms that don't support them natively)
    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    }
    
    // Clean up URI (remove trailing slash except for root)
    if ($uri !== '/' && substr($uri, -1) === '/') {
        $uri = rtrim($uri, '/');
    }
    
    // Dispatch the request
    $router->dispatch($method, $uri);
    
} catch (Exception $e) {
    // Log the error
    error_log("Application error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // Display error page
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    if ($environment === 'development') {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 4px; font-family: Arial, sans-serif;'>";
        echo "<h2>Application Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</div>";
    } else {
        include __DIR__ . '/../app/Views/errors/500.php';
    }
}

// Flush output buffer
ob_end_flush();