<?php

namespace Vaulty\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function init(): void
    {
        self::$config = [
            'host' => $_ENV['DB_HOST'] ?? 'db',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'dbname' => $_ENV['DB_NAME'] ?? 'vaulty',
            'username' => $_ENV['DB_USER'] ?? 'vaulty_user',
            'password' => $_ENV['DB_PASS'] ?? 'vaulty_pass',
            'charset' => 'utf8mb4'
        ];
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::init();
            
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                self::$config['host'],
                self::$config['port'],
                self::$config['dbname'],
                self::$config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$config['username'], self::$config['password'], $options);
            } catch (PDOException $e) {
                throw new \PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}