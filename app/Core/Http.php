<?php
declare(strict_types=1);

namespace App\Core;

final class Http
{
    public static function path(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        $normalized = rtrim($path, '/');
        return $normalized === '' ? '/' : $normalized;
    }

    public static function method(): string
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }
}
