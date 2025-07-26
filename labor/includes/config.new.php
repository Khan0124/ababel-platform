<?php
/**
 * New configuration file using environment variables
 * This will replace the old config.php
 */

// Prevent direct access
if (!defined('APP_RUNNING')) {
    define('APP_RUNNING', true);
}

// Load bootstrap file if not already loaded
if (!class_exists('\\App\\Core\\Database')) {
    require_once __DIR__ . '/../bootstrap/app.php';
}

// Get database connection for backward compatibility
$app = \App\Core\Application::getInstance();
$database = $app->get('db');
$conn = $database->getConnection();

// Define constants for backward compatibility
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_DATABASE'] ?? '');
define('DB_USER', $_ENV['DB_USERNAME'] ?? '');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? '');

// Additional configuration constants
define('SITE_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('MAX_UPLOAD_SIZE', $_ENV['MAX_UPLOAD_SIZE'] ?? '10M');
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 120);
define('LOGIN_MAX_ATTEMPTS', $_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5);
define('LOGIN_LOCKOUT_MINUTES', $_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15);