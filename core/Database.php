<?php
/**
 * Database Connection Singleton
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require APP_ROOT . '/config/database.php';

            if (!is_array($config)) {
                throw new RuntimeException('config/database.php must return an array');
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('A system error occurred. Please try again later.');
            }
        }
        return self::$instance;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
