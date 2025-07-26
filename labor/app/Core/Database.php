<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    public function __construct()
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
        ];

        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}"
            ];

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
        } catch (PDOException $e) {
            $this->logError($e);
            throw new \Exception('Database connection failed');
        }
    }

    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    public function prepare($sql)
    {
        return $this->getConnection()->prepare($sql);
    }

    public function query($sql)
    {
        return $this->getConnection()->query($sql);
    }

    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        return $this->getConnection()->commit();
    }

    public function rollBack()
    {
        return $this->getConnection()->rollBack();
    }

    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    private function logError(PDOException $e)
    {
        error_log(sprintf(
            "[%s] Database Error: %s in %s:%d",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}