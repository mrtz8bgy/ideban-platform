<?php
declare(strict_types=1);

use App\Core\Database;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function text_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    $count = preg_match_all('/./us', $value);
    return $count === false ? strlen($value) : $count;
}

function site_url(string $path = ''): string
{
    $base = rtrim((string) (getenv('APP_URL') ?: ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

function redirect_to(string $path, int $status = 303): never
{
    header('Location: ' . site_url($path), true, $status);
    exit;
}

function render_view(string $template, array $data = []): void
{
    $viewPath = APP_ROOT . '/app/Views/' . $template . '.php';
    if (!is_file($viewPath)) {
        http_response_code(500);
        echo 'View not found.';
        return;
    }
    extract($data, EXTR_SKIP);
    $pageTitle = $pageTitle ?? 'شبکه پردازان ایده‌بان الماس';
    $metaDescription = $metaDescription ?? 'راهکارهای فناوری اطلاعات، طراحی و توسعه، زیرساخت، امنیت و پشتیبانی.';
    $isAdmin = $isAdmin ?? str_starts_with($template, 'admin/');
    ob_start();
    require $viewPath;
    $content = (string) ob_get_clean();
    require APP_ROOT . '/app/Views/layout.php';
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['_csrf'] ?? '');
    $known = (string) ($_SESSION['_csrf_token'] ?? '');
    if ($sent === '' || $known === '' || !hash_equals($known, $sent)) {
        http_response_code(419);
        render_view('errors/419', ['pageTitle' => 'نشست نامعتبر']);
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $value = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($value) ? $value : null;
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function has_role(string $role): bool
{
    $user = current_user();
    return $user !== null && in_array($role, $user['roles'] ?? [], true);
}

function require_staff(?string $area = null): void
{
    if (current_user() === null) {
        redirect_to('/admin/login');
    }
    if ($area !== null && !can_manage($area)) {
        http_response_code(403);
        render_view('errors/403', ['pageTitle' => 'دسترسی غیرمجاز', 'isAdmin' => true]);
        exit;
    }
}

function can_manage(string $area): bool
{
    if (has_role('admin')) {
        return true;
    }
    $access = [
        'leads' => ['sales'],
        'services' => ['editor'],
        'plans' => ['editor'],
        'portfolio' => ['editor'],
    ];
    foreach ($access[$area] ?? [] as $role) {
        if (has_role($role)) {
            return true;
        }
    }
    return false;
}

function status_label(string $status): string
{
    return [
        'new' => 'درخواست جدید',
        'contacted' => 'تماس اولیه',
        'needs_assessment' => 'نیازسنجی',
        'proposal_sent' => 'ارسال پیشنهاد',
        'negotiation' => 'مذاکره',
        'won' => 'برنده‌شده',
        'lost' => 'از دست‌رفته',
    ][$status] ?? 'نامشخص';
}

function write_audit(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    try {
        $db = Database::connection();
        $user = current_user();
        $userId = $user['id'] ?? null;
        $stmt = $db->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issis', $userId, $action, $entityType, $entityId, $details);
        $stmt->execute();
    } catch (Throwable $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
