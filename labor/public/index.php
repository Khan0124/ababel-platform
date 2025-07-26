<?php
/**
 * Laboratory Management System
 * Main Entry Point (Nginx Compatible)
 */

// Define application constants
define('APP_RUNNING', true);
define('APP_START_TIME', microtime(true));

// Bootstrap the application  
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Handle the request
try {
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // Remove query string
    if (($pos = strpos($requestUri, '?')) !== false) {
        $requestUri = substr($requestUri, 0, $pos);
    }
    
    // Remove trailing slash except for root
    if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
        $requestUri = rtrim($requestUri, '/');
    }
    
    // Basic routing
    switch ($requestUri) {
        case '':
        case '/':
        case '/home':
            require __DIR__ . '/../resources/views/home.php';
            break;
            
        case '/admin/login':
            require __DIR__ . '/../admin/login.php';
            break;
            
        case '/admin/dashboard':
            require __DIR__ . '/../admin/dashboard.php';
            break;
            
        case '/admin/logout':
            require __DIR__ . '/../admin/logout.php';
            break;
            
        case '/admin/labs':
        case '/admin/labs_list':
            require __DIR__ . '/../admin/labs_list.php';
            break;
            
        case '/admin/add_lab':
            require __DIR__ . '/../admin/add_lab.php';
            break;
            
        case '/admin/edit_lab':
            require __DIR__ . '/../admin/edit_lab.php';
            break;
            
        case '/admin/delete_lab':
            require __DIR__ . '/../admin/delete_lab.php';
            break;
            
        case '/admin/toggle_lab':
            require __DIR__ . '/../admin/toggle_lab.php';
            break;
            
        case '/admin/subscriptions':
        case '/admin/subscriptions_list':
            require __DIR__ . '/../admin/subscriptions_list.php';
            break;
            
        case '/admin/tickets':
        case '/admin/tickets_list':
            require __DIR__ . '/../admin/tickets_list.php';
            break;
            
        case '/admin/activity_logs':
            require __DIR__ . '/../admin/activity_logs.php';
            break;
            
        case '/lab/login':
            require __DIR__ . '/../lab/lab_login.php';
            break;
            
        case '/lab/dashboard':
            require __DIR__ . '/../lab/lab_dashboard.php';
            break;
            
        case '/lab/logout':
            require __DIR__ . '/../lab/lab_logout.php';
            break;
            
        // Lab Pages Routes
        case '/lab/patients':
        case '/lab/patients_list':
            require __DIR__ . '/../lab/patients_list.php';
            break;
            
        case '/lab/add_patient':
            require __DIR__ . '/../lab/add_patient.php';
            break;
            
        case '/lab/edit_patient':
            require __DIR__ . '/../lab/edit_patient.php';
            break;
            
        case '/lab/view_patient':
            require __DIR__ . '/../lab/view_patient.php';
            break;
            
        case '/lab/exams':
        case '/lab/exams_list':
            require __DIR__ . '/../lab/exams_list.php';
            break;
            
        case '/lab/add_exam':
            require __DIR__ . '/../lab/add_exam.php';
            break;
            
        case '/lab/edit_exam':
            require __DIR__ . '/../lab/edit_exam.php';
            break;
            
        case '/lab/exam_list':
            require __DIR__ . '/../lab/exam_list.php';
            break;
            
        case '/lab/add_exam_for_patient':
            require __DIR__ . '/../lab/add_exam_for_patient.php';
            break;
            
        case '/lab/results':
        case '/lab/results_list':
            require __DIR__ . '/../lab/results_list.php';
            break;
            
        case '/lab/enter_exam_result':
            require __DIR__ . '/../lab/enter_exam_result.php';
            break;
            
        case '/lab/employees':
        case '/lab/employees_list':
            require __DIR__ . '/../lab/employees_list.php';
            break;
            
        case '/lab/add_employee':
            require __DIR__ . '/../lab/add_employee.php';
            break;
            
        case '/lab/edit_employee':
            require __DIR__ . '/../lab/edit_employee.php';
            break;
            
        case '/lab/cashbox':
            require __DIR__ . '/../lab/cashbox.php';
            break;
            
        case '/lab/stock':
        case '/lab/stock_list':
            require __DIR__ . '/../lab/stock_list.php';
            break;
            
        case '/lab/reports':
            require __DIR__ . '/../lab/reports.php';
            break;
            
        case '/lab/shifts':
        case '/lab/shift_list':
            require __DIR__ . '/../lab/shift_list.php';
            break;
            
        case '/lab/insurance_companies':
            require __DIR__ . '/../lab/insurance_companies.php';
            break;
            
        default:
            // Handle static assets
            if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $requestUri)) {
                // Try to serve from current public directory first
                $publicFile = __DIR__ . $requestUri;
                if (file_exists($publicFile) && is_file($publicFile)) {
                    $mimeTypes = [
                        'css' => 'text/css',
                        'js' => 'application/javascript',
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'ico' => 'image/x-icon',
                        'svg' => 'image/svg+xml',
                        'woff' => 'font/woff',
                        'woff2' => 'font/woff2',
                        'ttf' => 'font/ttf',
                        'eot' => 'application/vnd.ms-fontobject'
                    ];
                    
                    $ext = pathinfo($requestUri, PATHINFO_EXTENSION);
                    if (isset($mimeTypes[$ext])) {
                        header('Content-Type: ' . $mimeTypes[$ext]);
                    }
                    
                    readfile($publicFile);
                    exit;
                }
                
                // Try assets directory
                $assetFile = __DIR__ . '/../assets' . $requestUri;
                if (file_exists($assetFile) && is_file($assetFile)) {
                    $ext = pathinfo($requestUri, PATHINFO_EXTENSION);
                    if (isset($mimeTypes[$ext])) {
                        header('Content-Type: ' . $mimeTypes[$ext]);
                    }
                    readfile($assetFile);
                    exit;
                }
            }
            
            // Handle API routes
            if (strpos($requestUri, '/api/') === 0) {
                $apiFile = __DIR__ . '/..' . $requestUri;
                if (file_exists($apiFile) && is_file($apiFile)) {
                    require $apiFile;
                    exit;
                }
            }
            
            // Try to find the file in the directory structure
            $filePath = __DIR__ . '/..' . $requestUri;
            
            // Handle PHP files
            if (substr($requestUri, -4) === '.php') {
                if (file_exists($filePath) && is_file($filePath)) {
                    require $filePath;
                    exit;
                }
            }
            
            // Try adding .php extension
            $phpFile = $filePath . '.php';
            if (file_exists($phpFile) && is_file($phpFile)) {
                require $phpFile;
                exit;
            }
            
            // Try index.php in directory
            if (is_dir($filePath)) {
                $indexFile = $filePath . '/index.php';
                if (file_exists($indexFile)) {
                    require $indexFile;
                    exit;
                }
            }
            
            // 404 Not Found
            http_response_code(404);
            if (file_exists(__DIR__ . '/../resources/views/errors/404.php')) {
                require __DIR__ . '/../resources/views/errors/404.php';
            } else {
                echo "<h1>404 - Page Not Found</h1><p>The requested page could not be found.</p>";
            }
            break;
    }
    
} catch (Exception $e) {
    if (isset($app)) {
        $app->get('logger')->logException($e);
    }
    
    http_response_code(500);
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        if (file_exists(__DIR__ . '/../resources/views/errors/500.php')) {
            require __DIR__ . '/../resources/views/errors/500.php';
        } else {
            echo "<h1>500 - Internal Server Error</h1><p>An error occurred while processing your request.</p>";
        }
    }
}