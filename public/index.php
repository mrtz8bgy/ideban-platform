<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\PublicController;
use App\Core\Http;

require dirname(__DIR__) . '/app/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");

$path = Http::path();
$method = Http::method();
$public = new PublicController();
$admin = new AdminController();

try {
    if ($path === '/admin/login' && $method === 'GET') { $admin->loginForm(); exit; }
    if ($path === '/admin/login' && $method === 'POST') { $admin->loginPost(); exit; }
    if ($path === '/admin/logout' && $method === 'POST') { $admin->logout(); exit; }
    if ($path === '/admin/leads/update' && $method === 'POST') { $admin->updateLead(); exit; }
    if ($path === '/admin/services/save' && $method === 'POST') { $admin->saveService(); exit; }
    if ($path === '/admin/categories/save' && $method === 'POST') { $admin->saveCategory(); exit; }
    if ($path === '/admin/plans/save' && $method === 'POST') { $admin->savePlan(); exit; }
    if ($path === '/admin/prices/save' && $method === 'POST') { $admin->savePrice(); exit; }
    if ($path === '/admin/prices/hide' && $method === 'POST') { $admin->hidePrice(); exit; }
    if ($path === '/admin/portfolio/save' && $method === 'POST') { $admin->saveProject(); exit; }
    if ($path === '/admin/settings/save' && $method === 'POST') { $admin->saveSettings(); exit; }

    if (str_starts_with($path, '/admin')) {
        if ($method !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            render_view('errors/405', ['pageTitle' => 'روش درخواست پشتیبانی نمی‌شود', 'isAdmin' => true]);
            exit;
        }
        switch ($path) {
            case '/admin':
            case '/admin/': $admin->dashboard(); break;
            case '/admin/leads': $admin->leads(); break;
            case '/admin/services': $admin->services(); break;
            case '/admin/plans': $admin->plans(); break;
            case '/admin/prices': $admin->prices(); break;
            case '/admin/portfolio': $admin->portfolio(); break;
            case '/admin/settings': $admin->settings(); break;
            default:
                http_response_code(404);
                render_view('errors/404', ['pageTitle' => 'صفحه پیدا نشد', 'isAdmin' => true]);
        }
        exit;
    }

    if ($method === 'POST' && $path === '/request-quote') { $public->submitLead('quote'); exit; }
    if ($method === 'POST' && $path === '/contact') { $public->submitLead('consultation'); exit; }

    if ($method !== 'GET') {
        http_response_code(405);
        header('Allow: GET, POST');
        render_view('errors/405', ['pageTitle' => 'روش درخواست پشتیبانی نمی‌شود']);
        exit;
    }

    switch ($path) {
        case '/': $public->home(); break;
        case '/services': $public->services(); break;
        case '/pricing': $public->pricing(); break;
        case '/portfolio': $public->portfolio(); break;
        case '/contact': $public->contact(); break;
        case '/sitemap.xml': $public->sitemap(); break;
        case '/robots.txt': $public->robots(); break;
        default:
            if (preg_match('#^/services/([a-z0-9]+(?:-[a-z0-9]+)*)$#', $path, $matches)) {
                $public->service($matches[1]);
            } elseif (preg_match('#^/portfolio/([a-z0-9]+(?:-[a-z0-9]+)*)$#', $path, $matches)) {
                $public->project($matches[1]);
            } else {
                http_response_code(404);
                render_view('errors/404', ['pageTitle' => 'صفحه پیدا نشد']);
            }
    }
} catch (Throwable $exception) {
    error_log(sprintf('[%s] %s %s: %s', date('c'), $method, $path, $exception->getMessage()));
    http_response_code(500);
    $showDebug = (getenv('APP_DEBUG') ?: '0') === '1';
    render_view('errors/500', [
        'pageTitle' => 'خطای موقت',
        'debugMessage' => $showDebug ? $exception->getMessage() : null,
        'isAdmin' => str_starts_with($path, '/admin'),
    ]);
}
