<?php

namespace App\Models;
class DB
{
    private static $pdo; // database representation

    public static function getConnection()
    {
        if (self::$pdo) return self::$pdo;
        $config = require __DIR__ . '/../../config/config.php';
        $db = $config['db'];
        self::$pdo = new \PDO($db['dsn'], $db['user'], $db['pass'], $db['options']);
        return self::$pdo;
    }
}

