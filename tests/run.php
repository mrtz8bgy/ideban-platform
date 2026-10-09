<?php
declare(strict_types=1);

use App\Core\LeadValidator;

require dirname(__DIR__) . '/app/bootstrap.php';

$tests = 0;
$failures = [];
$assert = static function (bool $condition, string $label) use (&$tests, &$failures): void {
    $tests++;
    if (!$condition) {
        $failures[] = $label;
    }
};

$valid = LeadValidator::validate([
    'full_name' => 'آزمایش کاربر',
    'phone' => '۰۹۱۲۱۲۳۴۵۶۷',
    'email' => 'test@example.com',
    'company_name' => 'شرکت آزمایشی',
    'requested_service' => 'طراحی سایت',
    'description' => 'شرح تست',
    'lead_type' => 'quote',
]);
$assert($valid['errors'] === [], 'شماره فارسی و ورودی معتبر باید پذیرفته شود');
$assert($valid['data']['lead_type'] === 'quote', 'نوع سرنخ معتبر حفظ شود');

$invalid = LeadValidator::validate([
    'full_name' => '',
    'phone' => 'abc',
    'email' => 'not-an-email',
    'lead_type' => 'admin',
]);
$assert(isset($invalid['errors']['full_name']), 'نام خالی رد شود');
$assert(isset($invalid['errors']['phone']), 'شماره نامعتبر رد شود');
$assert(isset($invalid['errors']['email']), 'ایمیل نامعتبر رد شود');
$assert($invalid['data']['lead_type'] === 'contact', 'نوع سرنخ ناشناخته به contact محدود شود');

if ($failures !== []) {
    fwrite(STDERR, "ناموفق: " . implode('؛ ', $failures) . PHP_EOL);
    exit(1);
}
fwrite(STDOUT, "{$tests} آزمون اعتبارسنجی با موفقیت گذشت.\n");
