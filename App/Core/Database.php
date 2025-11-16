<?php

namespace App\Core;

use PDO;

class Database
{
    private static $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $connection = require __DIR__ . '/../../config/database.php';
            self::$instance = $connection;
        }
        return self::$instance;
    }
}
