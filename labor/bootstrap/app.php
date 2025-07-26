<?php

/**
 * Bootstrap the application
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load helper functions
require_once __DIR__ . '/../app/Helpers/functions.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $_ENV['SESSION_LIFETIME'] * 60 ?? 7200,
        'path' => '/',
        'domain' => parse_url($_ENV['APP_URL'], PHP_URL_HOST),
        'secure' => $_ENV['SESSION_SECURE_COOKIE'] ?? false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
}

// Initialize the application container
$app = new \App\Core\Application();

// Register core services
$app->singleton('db', function() {
    return new \App\Core\Database();
});

$app->singleton('auth', function() {
    return new \App\Services\AuthService();
});

$app->singleton('validator', function() {
    return new \App\Services\ValidationService();
});

$app->singleton('logger', function() {
    return new \App\Services\LoggerService();
});

$app->singleton('cache', function() {
    return new \App\Services\CacheService();
});

// Set global exception handler
set_exception_handler(function($exception) use ($app) {
    $app->get('logger')->error($exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        throw $exception;
    } else {
        http_response_code(500);
        include __DIR__ . '/../resources/views/errors/500.php';
        exit;
    }
});

return $app;