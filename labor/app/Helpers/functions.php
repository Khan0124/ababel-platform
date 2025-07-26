<?php

/**
 * Global Helper Functions
 */

if (!function_exists('app')) {
    function app($service = null)
    {
        $app = \App\Core\Application::getInstance();
        return $service ? $app->get($service) : $app;
    }
}

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = require __DIR__ . '/../../config/' . $keys[0] . '.php';
        
        array_shift($keys);
        foreach ($keys as $k) {
            $config = $config[$k] ?? $default;
        }
        
        return $config;
    }
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302)
    {
        header("Location: $url", true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    function back($fallback = '/')
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        redirect($referer);
    }
}

if (!function_exists('old')) {
    function old($key, $default = null)
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash($key = null, $message = null)
    {
        if ($key && $message) {
            $_SESSION['_flash'][$key] = $message;
        } elseif ($key) {
            $value = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $value;
        } else {
            return $_SESSION['_flash'] ?? [];
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        return \App\Services\CSRFService::generateToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field($method)
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        $baseUrl = rtrim(env('APP_URL'), '/');
        return $baseUrl . '/public/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $baseUrl = rtrim(env('APP_URL'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    function route($name, $params = [])
    {
        // Simple route helper - would be expanded in a full framework
        $routes = [
            'admin.login' => '/admin/login',
            'admin.dashboard' => '/admin/dashboard',
            'lab.login' => '/lab/login',
            'lab.dashboard' => '/lab/dashboard',
        ];
        
        $route = $routes[$name] ?? $name;
        
        if ($params) {
            foreach ($params as $key => $value) {
                $route = str_replace('{' . $key . '}', $value, $route);
            }
        }
        
        return url($route);
    }
}

if (!function_exists('auth')) {
    function auth($guard = null)
    {
        return app('auth');
    }
}

if (!function_exists('validate')) {
    function validate($data, $rules)
    {
        $validator = app('validator');
        
        if (!$validator->validate($data, $rules)) {
            $_SESSION['_errors'] = $validator->getErrors();
            $_SESSION['_old'] = $data;
            back();
        }
        
        return $data;
    }
}

if (!function_exists('errors')) {
    function errors($key = null)
    {
        $errors = $_SESSION['_errors'] ?? [];
        
        if ($key) {
            $value = $errors[$key] ?? [];
            return is_array($value) ? $value : [$value];
        }
        
        return $errors;
    }
}

if (!function_exists('hasErrors')) {
    function hasErrors($key = null)
    {
        if ($key) {
            return isset($_SESSION['_errors'][$key]);
        }
        
        return !empty($_SESSION['_errors']);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('logger')) {
    function logger($message = null, $context = [])
    {
        $logger = app('logger');
        
        if ($message) {
            $logger->info($message, $context);
        }
        
        return $logger;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'SAR')
    {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'Y-m-d')
    {
        if (!$date) return '';
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'Y-m-d H:i:s')
    {
        return formatDate($datetime, $format);
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'الآن';
        if ($time < 3600) return floor($time/60) . ' دقيقة';
        if ($time < 86400) return floor($time/3600) . ' ساعة';
        if ($time < 2592000) return floor($time/86400) . ' يوم';
        if ($time < 31536000) return floor($time/2592000) . ' شهر';
        
        return floor($time/31536000) . ' سنة';
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input, $type = 'string')
    {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
}

if (!function_exists('isRTL')) {
    function isRTL()
    {
        return true; // This is an Arabic system
    }
}

if (!function_exists('trans')) {
    function trans($key, $params = [])
    {
        // Simple translation function - would be expanded
        $translations = [
            'welcome' => 'مرحباً',
            'login' => 'تسجيل الدخول',
            'logout' => 'تسجيل الخروج',
            'dashboard' => 'لوحة التحكم',
            'settings' => 'الإعدادات',
            'profile' => 'الملف الشخصي',
            // Add more translations as needed
        ];
        
        $translation = $translations[$key] ?? $key;
        
        foreach ($params as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
        
        return $translation;
    }
}

// Clear flash data on new request
if (!isset($_SESSION['_flash_cleared'])) {
    unset($_SESSION['_flash']);
    unset($_SESSION['_errors']);
    unset($_SESSION['_old']);
    $_SESSION['_flash_cleared'] = true;
}