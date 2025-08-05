<?php
namespace App\Config;

class App {
    private static $config = [];
    
    public static function init() {
        self::loadEnvironment();
        self::setSecurityHeaders();
        self::configureErrorHandling();
        self::setTimezone();
    }
    
    private static function loadEnvironment() {
        // Load environment variables
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                        $value = $matches[1];
                    }
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        // Set default values
        self::$config = [
            'APP_ENV' => $_ENV['APP_ENV'] ?? 'production',
            'APP_DEBUG' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'APP_URL' => $_ENV['APP_URL'] ?? 'http://localhost',
            'APP_KEY' => $_ENV['APP_KEY'] ?? '',
            'DB_HOST' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'DB_PORT' => $_ENV['DB_PORT'] ?? '3306',
            'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? 'labor',
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? 'labor',
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
            'SESSION_LIFETIME' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
            'SESSION_SECURE_COOKIE' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'ENCRYPTION_KEY' => $_ENV['ENCRYPTION_KEY'] ?? $_ENV['APP_KEY'] ?? '',
            'TIMEZONE' => $_ENV['TIMEZONE'] ?? 'Africa/Cairo'
        ];
    }
    
    private static function setSecurityHeaders() {
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }
    }
    
    private static function configureErrorHandling() {
        if (self::get('APP_DEBUG')) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', dirname(__DIR__, 2) . '/logs/error.log');
        }
    }
    
    private static function setTimezone() {
        date_default_timezone_set(self::get('TIMEZONE'));
    }
    
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }
    
    public static function isDebug() {
        return self::get('APP_DEBUG');
    }
    
    public static function getAppKey() {
        $key = self::get('APP_KEY');
        if (empty($key)) {
            throw new \Exception('APP_KEY is not set. Please generate a secure key and add it to your .env file.');
        }
        return $key;
    }
} 