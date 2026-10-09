<?php
declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    public static function allow(string $key, int $limit = 4, int $windowSeconds = 900): bool
    {
        $now = time();
        $bucket = $_SESSION['_rate_limits'][$key] ?? [];
        $bucket = array_values(array_filter($bucket, static fn ($stamp) => is_int($stamp) && $stamp > $now - $windowSeconds));
        if (count($bucket) >= $limit) {
            $_SESSION['_rate_limits'][$key] = $bucket;
            return false;
        }
        $bucket[] = $now;
        $_SESSION['_rate_limits'][$key] = $bucket;
        return true;
    }
}
