<?php
declare(strict_types=1);

use App\Core\Database;

require dirname(__DIR__) . '/app/bootstrap.php';
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

function prompt_line(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string) fgets(STDIN));
}

function prompt_secret(string $label): string
{
    fwrite(STDOUT, $label);
    $hidden = false;
    if (DIRECTORY_SEPARATOR !== '\\' && function_exists('exec')) {
        @exec('stty -echo 2>/dev/null', $output, $status);
        $hidden = ($status ?? 1) === 0;
    }
    $value = trim((string) fgets(STDIN));
    if ($hidden) {
        @exec('stty echo 2>/dev/null');
        fwrite(STDOUT, PHP_EOL);
    }
    return $value;
}

$name = prompt_line('نام مدیر: ');
$email = strtolower(prompt_line('ایمیل مدیر: '));
if ($name === '' || text_length($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "نام یا ایمیل معتبر نیست.\n");
    exit(1);
}
$password = prompt_secret('گذرواژه (حداقل 12 نویسه): ');
$confirm = prompt_secret('تکرار گذرواژه: ');
if (strlen($password) < 12 || strlen($password) > 200 || !hash_equals($password, $confirm)) {
    fwrite(STDERR, "گذرواژه باید حداقل 12 نویسه باشد و دو بار یکسان وارد شود.\n");
    exit(1);
}

try {
    $db = Database::connection();
    $db->begin_transaction();
    $roleResult = $db->query("SELECT id FROM roles WHERE name='admin' LIMIT 1")->fetch_assoc();
    if (!$roleResult) {
        throw new RuntimeException('نقش admin پیدا نشد؛ ابتدا database/schema.sql و database/seed.sql را اجرا کنید.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hash);
    $stmt->execute();
    $userId = (int) $db->insert_id;
    $roleId = (int) $roleResult['id'];
    $roleStmt = $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
    $roleStmt->bind_param('ii', $userId, $roleId);
    $roleStmt->execute();
    $db->commit();
    fwrite(STDOUT, "مدیر با موفقیت ایجاد شد: {$email}\n");
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof mysqli) {
        $db->rollback();
    }
    fwrite(STDERR, 'ایجاد مدیر انجام نشد: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
