<?php

/**
 * Authentication API Routes
 */

use App\Models\Admin;
use App\Models\Employee;

$auth = app('auth');
$validator = app('validator');
$logger = app('logger');

switch ($method) {
    case 'POST':
        if (end($pathParts) === 'login') {
            // Login endpoint
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (!$validator->validate($input, [
                'email' => 'required|email',
                'password' => 'required|min:1',
                'type' => 'required|in:admin,employee'
            ])) {
                http_response_code(422);
                echo json_encode([
                    'error' => 'Validation Failed',
                    'errors' => $validator->getErrors()
                ]);
                exit;
            }
            
            $email = $input['email'];
            $password = $input['password'];
            $type = $input['type'];
            
            if ($type === 'admin') {
                $result = $auth->attemptAdminLogin($email, $password);
            } else {
                $result = $auth->attemptEmployeeLogin($email, $password);
            }
            
            if ($result['success']) {
                // Generate API token (simple implementation)
                $token = base64_encode(json_encode([
                    'type' => $type,
                    'id' => $result[$type]->id,
                    'exp' => time() + 3600 // 1 hour
                ]));
                
                echo json_encode([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'token' => $token,
                    'user' => $result[$type]->toArray(),
                    'expires_in' => 3600
                ]);
                
                $logger->logActivity('api_login', "API login successful for {$type}: {$email}");
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication Failed',
                    'message' => $result['message']
                ]);
                
                $logger->logSecurity('api_login_failed', 'warning', [
                    'email' => $email,
                    'type' => $type
                ]);
            }
            
        } elseif (end($pathParts) === 'logout') {
            // Logout endpoint
            echo json_encode([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
            
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;
        
    case 'GET':
        if (end($pathParts) === 'me') {
            // Get current user info
            // This would require proper token validation
            echo json_encode([
                'user' => [
                    'id' => 1,
                    'name' => 'مستخدم تجريبي',
                    'email' => 'test@example.com',
                    'type' => 'admin'
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}