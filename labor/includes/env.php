<?php
/**
 * Environment Configuration Loader
 * Loads environment variables from .env file
 */

class Env {
    private static $variables = [];
    private static $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        $envFile = $path ?: dirname(__DIR__) . '/.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Please create one from .env.example');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                self::$variables[$key] = $value;
                
                // Also set in $_ENV for compatibility
                $_ENV[$key] = $value;
                
                // Set in putenv for getenv() compatibility
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
    }

    /**
     * Get an environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$variables[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if an environment variable exists
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$variables[$key]) || isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * Get all environment variables
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$variables;
    }
}