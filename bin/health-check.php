<?php
declare(strict_types=1);

use App\Core\Database;

require dirname(__DIR__) . '/app/bootstrap.php';
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

try {
    $db = Database::connection();
    $required = ['users','roles','user_roles','service_categories','services','plans','plan_features','price_sources','price_records','portfolio_projects','leads','lead_notes','site_settings','audit_logs'];
    $missing = [];
    $check = $db->prepare('SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');
    foreach ($required as $table) {
        $check->bind_param('s', $table);
        $check->execute();
        if ((int) $check->get_result()->fetch_assoc()['c'] !== 1) {
            $missing[] = $table;
        }
    }
    if ($missing !== []) {
        fwrite(STDERR, 'جداول پایگاه داده ناقص‌اند: ' . implode(', ', $missing) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "اتصال MySQLi برقرار است و جداول فاز اول در دسترس‌اند.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'بررسی ناموفق: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
