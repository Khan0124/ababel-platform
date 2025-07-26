<?php

namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class LoggerService
{
    private $logger;
    private $logPath;
    
    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../storage/logs/';
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        $this->logger = new Logger('labor');
        $this->setupHandlers();
    }
    
    private function setupHandlers()
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        
        // Daily rotating file handler
        $handler = new RotatingFileHandler(
            $this->logPath . 'labor.log',
            30, // Keep 30 days of logs
            $this->getLogLevel()
        );
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
        
        // Separate error log
        if ($_ENV['APP_ENV'] === 'production') {
            $errorHandler = new StreamHandler(
                $this->logPath . 'error.log',
                Logger::ERROR
            );
            $errorHandler->setFormatter($formatter);
            $this->logger->pushHandler($errorHandler);
        }
        
        // Console output in development
        if ($_ENV['APP_ENV'] !== 'production') {
            $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            $consoleHandler->setFormatter($formatter);
            $this->logger->pushHandler($consoleHandler);
        }
    }
    
    private function getLogLevel()
    {
        $level = strtoupper($_ENV['LOG_LEVEL'] ?? 'DEBUG');
        
        $levels = [
            'DEBUG' => Logger::DEBUG,
            'INFO' => Logger::INFO,
            'NOTICE' => Logger::NOTICE,
            'WARNING' => Logger::WARNING,
            'ERROR' => Logger::ERROR,
            'CRITICAL' => Logger::CRITICAL,
            'ALERT' => Logger::ALERT,
            'EMERGENCY' => Logger::EMERGENCY,
        ];
        
        return $levels[$level] ?? Logger::DEBUG;
    }
    
    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $this->enrichContext($context));
    }
    
    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $this->enrichContext($context));
    }
    
    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $this->enrichContext($context));
    }
    
    public function error($message, array $context = [])
    {
        $this->logger->error($message, $this->enrichContext($context));
    }
    
    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $this->enrichContext($context));
    }
    
    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $this->enrichContext($context));
    }
    
    public function info($message, array $context = [])
    {
        $this->logger->info($message, $this->enrichContext($context));
    }
    
    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $this->enrichContext($context));
    }
    
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $this->enrichContext($context));
    }
    
    private function enrichContext(array $context)
    {
        // Add common context
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? null;
        $context['request_method'] = $_SERVER['REQUEST_METHOD'] ?? null;
        
        // Add user context
        if (isset($_SESSION['admin_id'])) {
            $context['admin_id'] = $_SESSION['admin_id'];
            $context['admin_name'] = $_SESSION['admin_name'] ?? null;
        } elseif (isset($_SESSION['employee_id'])) {
            $context['employee_id'] = $_SESSION['employee_id'];
            $context['employee_name'] = $_SESSION['employee_name'] ?? null;
            $context['lab_id'] = $_SESSION['lab_id'] ?? null;
        }
        
        return $context;
    }
    
    public function logActivity($action, $description = null, array $extra = [])
    {
        $context = [
            'action' => $action,
            'description' => $description,
        ];
        
        if ($extra) {
            $context['extra'] = $extra;
        }
        
        $this->info("Activity: {$action}", $context);
    }
    
    public function logSecurity($event, $severity = 'warning', array $context = [])
    {
        $context['security_event'] = $event;
        
        switch ($severity) {
            case 'critical':
                $this->critical("Security Event: {$event}", $context);
                break;
            case 'error':
                $this->error("Security Event: {$event}", $context);
                break;
            case 'info':
                $this->info("Security Event: {$event}", $context);
                break;
            default:
                $this->warning("Security Event: {$event}", $context);
        }
    }
    
    public function logPerformance($operation, $duration, array $context = [])
    {
        $context['operation'] = $operation;
        $context['duration_ms'] = $duration;
        
        if ($duration > 1000) { // More than 1 second
            $this->warning("Slow operation: {$operation} took {$duration}ms", $context);
        } else {
            $this->info("Performance: {$operation} completed in {$duration}ms", $context);
        }
    }
    
    public function logException(\Throwable $exception, array $context = [])
    {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
        
        $this->error($exception->getMessage(), $context);
    }
}