<?php
// app/Database/Database.php - Centralized PDO Database Connection Singleton

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static bool $connectionAttempted = false;

    public static function getConnection(): ?PDO
    {
        if (!self::$connectionAttempted) {
            self::$connectionAttempted = true;
            $host = '127.0.0.1';
            $db = 'hotelmaps_system';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Return null on failure so fallbacks work seamlessly
                self::$instance = null;
            }
        }
        return self::$instance;
    }
}
