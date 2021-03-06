<?php declare(strict_types=1);


namespace Source\Core;

use PDO;
use PDOException;
use Exception;

class Connection
{
    private const OPTIONS = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ];

    private ?PDO $conn = null;
    private static ?Connection $instance = null;

    /** @var Exception|PDOException */
    public static $error;

    final private function __construct()
    {
        $this->conn = new PDO(
            "mysql:host=" . SQL_DB_HOST . ";dbname=" . SQL_DB_NAME,
            SQL_DB_USER,
            SQL_DB_PASS,
            self::OPTIONS
        );
    }

    public static function getInstance(): ?Connection
    {
        if (!self::$instance)
            self::$instance = new Connection;
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
