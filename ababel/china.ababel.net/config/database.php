<?php
/**
 * Database Configuration
 * Supports environment-based configuration for better security
 */

// Load environment variables if .env file exists
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

// Environment detection
$environment = $_ENV['APP_ENV'] ?? 'production';

// Base configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'china_ababel',
    'username' => $_ENV['DB_USER'] ?? 'china_ababel',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true, // Enable persistent connections
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]
];

// Development-specific settings
if ($environment === 'development') {
    $config['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Production-specific settings
if ($environment === 'production') {
    $config['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

return $config;