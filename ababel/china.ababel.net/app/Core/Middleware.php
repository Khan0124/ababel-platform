<?php
namespace App\Core;

use Exception;

/**
 * Middleware System
 * Handles request processing, security, and authentication in a centralized way
 */
abstract class Middleware
{
    protected $next;
    protected $security;
    
    public function __construct()
    {
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Set next middleware in chain
     */
    public function setNext(Middleware $next)
    {
        $this->next = $next;
        return $next;
    }
    
    /**
     * Process the request
     */
    public function process($request)
    {
        // Execute current middleware
        $this->handle($request);
        
        // Pass to next middleware if exists
        if ($this->next) {
            $this->next->process($request);
        }
    }
    
    /**
     * Handle the request - to be implemented by subclasses
     */
    abstract protected function handle($request);
}

/**
 * Security Middleware
 * Handles security headers, CSRF protection, and input validation
 */
class SecurityMiddleware extends Middleware
{
    protected function handle($request)
    {
        // Set security headers
        $this->setSecurityHeaders();
        
        // Validate CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
        }
        
        // Sanitize input
        $this->sanitizeInput();
        
        // Rate limiting
        $this->checkRateLimit();
    }
    
    private function setSecurityHeaders()
    {
        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
        ];
        
        foreach ($headers as $header => $value) {
            if (!headers_sent()) {
                header("$header: $value");
            }
        }
    }
    
    private function validateCSRF()
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !$this->security->validateCSRFToken($token)) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            } else {
                header('Location: /error/403');
                exit;
            }
        }
    }
    
    private function sanitizeInput()
    {
        if ($_POST) {
            $_POST = $this->security->sanitizeInput($_POST);
        }
        
        if ($_GET) {
            $_GET = $this->security->sanitizeInput($_GET);
        }
    }
    
    private function checkRateLimit()
    {
        $identifier = $this->security->getClientIP();
        
        if (!$this->security->checkRateLimit($identifier, 1000, 3600)) {
            if ($this->isAjaxRequest()) {
                http_response_code(429);
                echo json_encode(['error' => 'Too many requests']);
                exit;
            } else {
                header('Location: /error/429');
                exit;
            }
        }
    }
    
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * Authentication Middleware
 * Handles user authentication and session management
 */
class AuthMiddleware extends Middleware
{
    protected function handle($request)
    {
        // Start secure session
        $this->security->startSecureSession();
        
        // Validate session
        if (!$this->security->validateSession()) {
            $this->redirectToLogin();
        }
        
        // Check session timeout
        if ($this->isSessionExpired()) {
            $this->security->destroySession();
            $this->redirectToLogin();
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
    }
    
    private function isSessionExpired()
    {
        $timeout = 3600; // 1 hour
        return isset($_SESSION['last_activity']) && 
               (time() - $_SESSION['last_activity']) > $timeout;
    }
    
    private function redirectToLogin()
    {
        if ($this->isAjaxRequest()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        } else {
            header('Location: /login');
            exit;
        }
    }
    
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * Logging Middleware
 * Handles request logging and monitoring
 */
class LoggingMiddleware extends Middleware
{
    protected function handle($request)
    {
        // Log request
        $this->logRequest();
        
        // Monitor for suspicious activity
        $this->monitorSuspiciousActivity();
    }
    
    private function logRequest()
    {
        $db = Database::getInstance();
        
        try {
            $sql = "INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, metadata, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $db->query($sql, [
                $_SESSION['user_id'] ?? null,
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'],
                $this->security->getClientIP(),
                $this->security->getUserAgent(),
                json_encode([
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'uri' => $_SERVER['REQUEST_URI'],
                    'referer' => $_SERVER['HTTP_REFERER'] ?? null
                ])
            ]);
        } catch (Exception $e) {
            error_log("Failed to log request: " . $e->getMessage());
        }
    }
    
    private function monitorSuspiciousActivity()
    {
        $ip = $this->security->getClientIP();
        $userAgent = $this->security->getUserAgent();
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/script/i',
            '/javascript/i',
            '/<script/i',
            '/union.*select/i',
            '/drop.*table/i',
            '/insert.*into/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent) || 
                preg_match($pattern, $_SERVER['REQUEST_URI']) ||
                preg_match($pattern, json_encode($_POST))) {
                
                $this->security->logSecurityEvent('suspicious_activity', 'Suspicious request detected', [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'uri' => $_SERVER['REQUEST_URI'],
                    'pattern' => $pattern
                ]);
                
                break;
            }
        }
    }
}

/**
 * Error Handling Middleware
 * Handles error processing and logging
 */
class ErrorHandlingMiddleware extends Middleware
{
    protected function handle($request)
    {
        // Set error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        
        // Register shutdown function
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        
        $this->logError($error);
        
        if (ini_get('display_errors')) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "<strong>Error:</strong> $errstr<br>";
            echo "<strong>File:</strong> $errfile:$errline";
            echo "</div>";
        }
        
        return true;
    }
    
    public function handleException($exception)
    {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        $this->logError($error);
        
        if ($this->isAjaxRequest()) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        } else {
            $this->displayErrorPage($error);
        }
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logError($error);
            $this->displayErrorPage($error);
        }
    }
    
    private function logError($error)
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    private function displayErrorPage($error)
    {
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        include __DIR__ . '/../Views/errors/500.php';
        exit;
    }
    
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
} 