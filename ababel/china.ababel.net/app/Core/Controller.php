<?php
// app/Core/Controller.php
namespace App\Core;

use Exception;

/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    protected $db;
    protected $security;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->security = new SecurityManager();
    }
    
    /**
     * Render a view with data
     */
    protected function view($view, $data = [])
    {
        try {
            $viewFile = __DIR__ . '/../Views/' . $view . '.php';
            
            if (!file_exists($viewFile)) {
                throw new Exception("View not found: $view");
            }
            
            // Extract data to make variables available in view
            extract($data);
            
            // Start output buffering
            ob_start();
            require $viewFile;
            $content = ob_get_clean();
            
            echo $content;
            
        } catch (Exception $e) {
            $this->handleError($e, 'View rendering failed');
        }
    }
    
    /**
     * Redirect to another URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header("Location: $url");
        } else {
            echo "<script>window.location.href='$url';</script>";
        }
        exit;
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get POST data with validation
     */
    protected function getPost($key, $default = null, $sanitize = true)
    {
        $value = $_POST[$key] ?? $default;
        
        if ($sanitize && $value !== null) {
            $value = $this->security->sanitizeInput($value);
        }
        
        return $value;
    }
    
    /**
     * Get GET data with validation
     */
    protected function getGet($key, $default = null, $sanitize = true)
    {
        $value = $_GET[$key] ?? $default;
        
        if ($sanitize && $value !== null) {
            $value = $this->security->sanitizeInput($value);
        }
        
        return $value;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF()
    {
        if ($this->isPost()) {
            $token = $this->getPost('csrf_token');
            if (!$this->security->validateCSRFToken($token)) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
        }
    }
    
    /**
     * Generate CSRF token for forms
     */
    protected function generateCSRFToken()
    {
        return $this->security->generateCSRFToken();
    }
    
    /**
     * Check user permissions
     */
    protected function hasPermission($requiredRole)
    {
        $roleHierarchy = [
            'viewer' => 1,
            'accountant' => 2,
            'admin' => 3
        ];
        
        $userRole = $_SESSION['user_role'] ?? 'viewer';
        
        return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }
    
    /**
     * Require specific permission
     */
    protected function requirePermission($permission)
    {
        if (!$this->hasPermission($permission)) {
            $this->json(['error' => 'Insufficient permissions'], 403);
        }
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $requiredFields)
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '$field' is required";
            }
        }
        
        if (!empty($errors)) {
            $this->json(['errors' => $errors], 400);
        }
        
        return true;
    }
    
    /**
     * Validate email format
     */
    protected function validateEmail($email)
    {
        return $this->security->validateEmail($email);
    }
    
    /**
     * Validate password strength
     */
    protected function validatePassword($password)
    {
        return $this->security->validatePasswordStrength($password);
    }
    
    /**
     * Handle errors gracefully
     */
    protected function handleError(Exception $e, $context = '')
    {
        $errorMessage = $context ? "$context: " . $e->getMessage() : $e->getMessage();
        
        if ($this->isAjax()) {
            $this->json(['error' => $errorMessage], 500);
        } else {
            // Log error
            error_log($errorMessage);
            
            // Show user-friendly error page
            $this->view('errors/500', [
                'title' => 'Error',
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    
    /**
     * Log user activity
     */
    protected function logActivity($action, $description = '', $metadata = [])
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $ip = $this->security->getClientIP();
            $userAgent = $this->security->getUserAgent();
            
            $sql = "INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, metadata, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $userId,
                $action,
                $description,
                $ip,
                $userAgent,
                json_encode($metadata)
            ]);
            
        } catch (Exception $e) {
            // Don't let logging errors break the application
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}