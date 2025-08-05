<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Database Connection Manager
 * Implements singleton pattern with connection pooling and proper error handling
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    private $connectionCount = 0;
    private $maxConnections = 10;
    
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    /**
     * Get database instance (singleton pattern)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection with proper error handling
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
            
            // Set additional connection attributes
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->connectionCount++;
            
        } catch (PDOException $e) {
            $this->logError('Database connection failed', $e);
            throw new Exception('Database connection failed. Please try again later.');
        }
    }
    
    /**
     * Get database connection
     */
    public function getConnection()
    {
        if (!$this->connection || !$this->isConnected()) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Check if connection is active
     */
    private function isConnected()
    {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Execute a prepared statement with parameters
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Failed to prepare statement');
            }
            
            $stmt->execute($params);
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logError('Database query failed', $e, ['sql' => $sql, 'params' => $params]);
            throw new Exception('Database operation failed. Please try again later.');
        }
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        return $this->getConnection()->rollBack();
    }
    
    /**
     * Check if in transaction
     */
    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }
    
    /**
     * Execute transaction with callback
     */
    public function transaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Log database errors
     */
    private function logError($message, Exception $exception, array $context = [])
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nContext: %s\n",
            date('Y-m-d H:i:s'),
            $message,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            json_encode($context)
        );
        
        $logFile = __DIR__ . '/../../storage/logs/database.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Close database connection
     */
    public function close()
    {
        $this->connection = null;
        self::$instance = null;
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    private function __wakeup() {}
}