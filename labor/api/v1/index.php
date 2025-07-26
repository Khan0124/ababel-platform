<?php

/**
 * REST API Entry Point
 * Version 1.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Bootstrap application
require_once __DIR__ . '/../../bootstrap/app.php';

try {
    // Get request details
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api/v1', '', $path);
    $pathParts = array_filter(explode('/', $path));
    
    // Basic routing
    $resource = $pathParts[1] ?? null;
    $id = $pathParts[2] ?? null;
    
    // Authentication check
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($authHeader) && !in_array($resource, ['auth', 'docs'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => 'API key required']);
        exit;
    }
    
    // Route the request
    switch ($resource) {
        case 'auth':
            require __DIR__ . '/routes/auth.php';
            break;
            
        case 'patients':
            require __DIR__ . '/routes/patients.php';
            break;
            
        case 'exams':
            require __DIR__ . '/routes/exams.php';
            break;
            
        case 'results':
            require __DIR__ . '/routes/results.php';
            break;
            
        case 'reports':
            require __DIR__ . '/routes/reports.php';
            break;
            
        case 'docs':
            require __DIR__ . '/docs.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Not Found',
                'message' => 'API endpoint not found',
                'available_endpoints' => [
                    '/api/v1/auth',
                    '/api/v1/patients',
                    '/api/v1/exams',
                    '/api/v1/results',
                    '/api/v1/reports',
                    '/api/v1/docs'
                ]
            ]);
            break;
    }
    
} catch (Exception $e) {
    app('logger')->logException($e);
    
    http_response_code(500);
    
    if (env('APP_DEBUG') === 'true') {
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred'
        ]);
    }
}