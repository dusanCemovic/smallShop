<?php

namespace App\Models;
class DB
{
    private static $config; // config file
    private static $pdo; // database representation

    public static function getConfig() {
        if (self::$config) {
            return self::$config;
        }

        self::$config = require __DIR__ . '/../../config/config.php';
        return self::$config;
    }

    public static function getConnection()
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $db = self::$config['db'];
        self::$pdo = new \PDO($db['dsn'], $db['user'], $db['pass'], $db['options']);
        return self::$pdo;
    }
}

