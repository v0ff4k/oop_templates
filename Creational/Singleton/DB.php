<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 */

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

    private final function __clone() {}

    private final function __wakeup() {}

    private final function __construct()
    {

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
        ];

        $this->instance = new PDO(
            'mysql:host='.$this->host.';dbname='.$this->name,
            $this->user,
            $this->pass,
            $options
        );
    }

    public static function connect($host, $name, $user, $pass)
    {
        if (self::$conn != null) {
            return self::$conn;
        }

        return new self;
    }
}

//client class with calling:::
class PDOConnection
{
    use DB;
}

$pdo = PDOConnection::connect('127.0.0.1', 'data_name', 'root', 'pass');
