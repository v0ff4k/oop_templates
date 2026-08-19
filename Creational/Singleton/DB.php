<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 * upd: 5.01.24 - 12:27
 * upd: 18.08.26 - 22:59
 */

namespace Creational\Singleton;

use \PDO;

trait DB
{
    /** @var null self */
    private static $conn = null;

    /** @var string $host */
    private $host;

    /** @var string $host */
    private $name;

    /** @var string $user */
    private $user;

    /** @var string $pass */
    private $pass;

    /** @var PDO $pdo */
    private $pdo;

    final private function __construct()
    {
        $this->loadConfig();

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
        ];

        $this->pdo = new PDO(
            'mysql:host=' . $this->host . ';dbname=' . $this->name,
            $this->user,
            $this->pass,
            $options
        );
    }

    /**
     * COnfig init, get from .env/file/others
     */
    private function loadConfig(): void
    {
        $this->host = 'hostname';
        $this->name = 'databaseName';
        $this->user = 'username';
        $this->pass = 'password';
    }

    public static function connect()
    {
        if (self::$conn != null) {
            return self::$conn;
        }

        return new self;
    }

    // disable copy on clone

    /**
     * Get getPdo
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    final private function __clone()
    {
    }

    // disable on deserialization(json->obj)
    final private function __wakeup()
    {
    }
}


/**
 * Клиентский код
 */
class PDOConnection
{
    use DB;
}

$conn = PDOConnection::connect();
$stmt = $conn->getPdo()->prepare('SELECT COUNT(*) as count FROM users');
$stmt->execute([]);
/** @var array $result */
$result = $stmt->fetchAll();
