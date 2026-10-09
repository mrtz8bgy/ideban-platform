<?php
declare(strict_types=1);

namespace App\Core;

use mysqli;
use RuntimeException;

final class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }
        if (!extension_loaded('mysqli')) {
            throw new RuntimeException('PHP extension mysqli is required.');
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: '';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: '';
        $port = (int) (getenv('DB_PORT') ?: 3306);

        if ($user === '' || $name === '') {
            throw new RuntimeException('Database configuration is incomplete.');
        }

        $db = new mysqli($host, $user, $pass, $name, $port);
        $db->set_charset('utf8mb4');
        self::$connection = $db;
        return self::$connection;
    }
}
