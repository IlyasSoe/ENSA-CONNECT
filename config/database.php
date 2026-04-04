<?php
class Database
{
    private static $instance = null;
    private static $host = 'localhost';
    private static $dbname = 'ensa_connect';
    private static $user = 'root';
    private static $pass = '';

    public static function connect()
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8";
            self::$instance = new PDO($dsn, self::$user, self::$pass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
        }
        return self::$instance;
    }
}