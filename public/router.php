<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicRoot = realpath(__DIR__);
$file = realpath(__DIR__ . $path);
if ($path !== '/' && $publicRoot !== false && $file !== false && str_starts_with($file, $publicRoot . DIRECTORY_SEPARATOR) && is_file($file)) {
    return false;
}
require __DIR__ . '/index.php';
