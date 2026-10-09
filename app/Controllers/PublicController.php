<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\LeadValidator;
use App\Core\RateLimiter;
use Throwable;

final class PublicController
{
    public function home(): void
    {
        $db = Database::connection();
        $services = $db->query('SELECT s.id, s.title, s.slug, s.short_description, c.title AS category_title, c.slug AS category_slug FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.is_active=1 AND c.is_active=1 ORDER BY s.display_order, s.id LIMIT 6')->fetch_all(MYSQLI_ASSOC);
        $plans = $this->getPlans($db);
        $settings = $this->getSettings($db);
        render_view('home', [
            'pageTitle' => 'شبکه پردازان ایده‌بان الماس | راهکارهای فناوری کسب‌وکار',
            'metaDescription' => 'طراحی و توسعه وب‌سایت و نرم‌افزار، DevOps، زیرساخت سرور، شبکه، امنیت و پشتیبانی IT برای کسب‌وکارها.',
            'services' => $services,
            'plans' => $plans,
            'settings' => $settings,
            'selectedService' => null,
            'leadSource' => $this->leadSource(),
        ]);
    }

    public function services(): void
    {
        $db = Database::connection();
        $services = $db->query('SELECT s.id, s.title, s.slug, s.short_description, c.title AS category_title FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.is_active=1 AND c.is_active=1 ORDER BY c.display_order, s.display_order, s.id')->fetch_all(MYSQLI_ASSOC);
        render_view('services', [
            'pageTitle' => 'خدمات فناوری اطلاعات | ایده‌بان',
            'metaDescription' => 'فهرست خدمات طراحی سایت و نرم‌افزار، DevOps و سرور، شبکه، امنیت و پشتیبانی IT.',
            'services' => $services,
        ]);
    }

    public function service(string $slug): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT s.*, c.title AS category_title FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.slug=? AND s.is_active=1 AND c.is_active=1 LIMIT 1');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        if (!$service) {
            http_response_code(404);
            render_view('errors/404', ['pageTitle' => 'خدمت پیدا نشد']);
            return;
        }
        $stmt = $db->prepare('SELECT p.id, p.title, p.slug, p.audience, p.summary FROM plans p WHERE p.is_published=1 AND (p.service_id=? OR p.service_id IS NULL) ORDER BY p.display_order LIMIT 3');
        $serviceId = (int) $service['id'];
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $relatedPlans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $priceInfo = $this->publicPriceInfo($db, 'service_id', (int) $service['id']);
        render_view('service-detail', [
            'pageTitle' => $service['title'] . ' | ایده‌بان',
            'metaDescription' => $service['short_description'],
            'service' => $service,
            'priceInfo' => $priceInfo,
            'plans' => $relatedPlans,
        ]);
    }

    public function pricing(): void
    {
        $plans = $this->getPlans(Database::connection());
        render_view('pricing', [
            'pageTitle' => 'پلن‌ها و تعرفه‌ها | ایده‌بان',
            'metaDescription' => 'مقایسه پلن‌های پایه، حرفه‌ای و سازمانی خدمات فناوری اطلاعات. قیمت نهایی پس از نیازسنجی اعلام می‌شود.',
            'plans' => $plans,
        ]);
    }

    public function portfolio(): void
    {
        $db = Database::connection();
        $projects = $db->query('SELECT id, title, slug, client_label, category, problem, solution, technologies, measurable_result, public_url, executed_on FROM portfolio_projects WHERE is_published=1 AND publication_approved=1 ORDER BY display_order, executed_on DESC')->fetch_all(MYSQLI_ASSOC);
        render_view('portfolio', [
            'pageTitle' => 'نمونه‌کارها | ایده‌بان',
            'metaDescription' => 'مطالعات موردی و پروژه‌هایی که انتشار عمومی آن‌ها تأیید شده است.',
            'projects' => $projects,
        ]);
    }

    public function project(string $slug): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM portfolio_projects WHERE slug=? AND is_published=1 AND publication_approved=1 LIMIT 1');
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $project = $stmt->get_result()->fetch_assoc();
        if (!$project) {
            http_response_code(404);
            render_view('errors/404', ['pageTitle' => 'نمونه‌کار پیدا نشد']);
            return;
        }
        $mediaStmt = $db->prepare('SELECT media_type, media_url, alt_text FROM project_media WHERE project_id=? ORDER BY display_order, id');
        $projectId = (int) $project['id'];
        $mediaStmt->bind_param('i', $projectId);
        $mediaStmt->execute();
        render_view('portfolio-detail', [
            'pageTitle' => $project['title'] . ' | نمونه‌کار ایده‌بان',
            'metaDescription' => $project['problem'] ?: 'مطالعه موردی و شرح پروژه فناوری اطلاعات.',
            'project' => $project,
            'media' => $mediaStmt->get_result()->fetch_all(MYSQLI_ASSOC),
        ]);
    }

    public function sitemap(): never
    {
        $db = Database::connection();
        $paths = ['/', '/services', '/pricing', '/portfolio', '/contact'];
        $services = $db->query('SELECT s.slug FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.is_active=1 AND c.is_active=1')->fetch_all(MYSQLI_ASSOC);
        foreach ($services as $service) { $paths[] = '/services/' . $service['slug']; }
        $projects = $db->query('SELECT slug FROM portfolio_projects WHERE is_published=1 AND publication_approved=1')->fetch_all(MYSQLI_ASSOC);
        foreach ($projects as $project) { $paths[] = '/portfolio/' . $project['slug']; }
        header('Content-Type: application/xml; charset=utf-8');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($paths as $path) {
            $xml .= '<url><loc>' . htmlspecialchars(site_url($path), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
        }
        echo $xml . '</urlset>';
        exit;
    }

    public function robots(): never
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'User-agent: *' . PHP_EOL . 'Allow: /' . PHP_EOL . 'Disallow: /admin' . PHP_EOL . 'Sitemap: ' . site_url('/sitemap.xml') . PHP_EOL;
        exit;
    }

    public function contact(): void
    {
        $db = Database::connection();
        $services = $db->query('SELECT s.id, s.title FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.is_active=1 AND c.is_active=1 ORDER BY s.display_order, s.title')->fetch_all(MYSQLI_ASSOC);
        render_view('contact', [
            'pageTitle' => 'درخواست مشاوره و تماس با ما | ایده‌بان',
            'metaDescription' => 'برای نیازسنجی خدمات فناوری اطلاعات یا دریافت پیش‌فاکتور با شبکه پردازان ایده‌بان الماس در ارتباط باشید.',
            'services' => $services,
            'settings' => $this->getSettings($db),
            'leadSource' => $this->leadSource(),
        ]);
    }

    public function submitLead(string $leadType): void
    {
        verify_csrf();
        if (!in_array($leadType, ['consultation', 'quote', 'contact'], true)) {
            $leadType = 'contact';
        }

        // Silent honeypot for basic bot filtering.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            flash('success', 'درخواست شما ثبت شد. در صورت نیاز با شما تماس می‌گیریم.');
            redirect_to('/#contact');
        }

        if ((string) ($_POST['privacy_consent'] ?? '') !== '1') {
            flash('error', 'برای ثبت درخواست، تأیید استفاده از اطلاعات تماس لازم است.');
            redirect_to('/#contact');
        }

        $result = LeadValidator::validate($_POST);
        if ($result['errors'] !== []) {
            flash('error', implode(' ', array_values($result['errors'])));
            redirect_to('/#contact');
        }

        if (!RateLimiter::allow('lead_form', 4, 900)) {
            flash('error', 'تعداد درخواست‌ها در بازه کوتاه زیاد است. کمی بعد دوباره تلاش کنید.');
            redirect_to('/#contact');
        }

        $data = $result['data'];
        $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
        $serviceId = ($serviceId !== false && $serviceId > 0) ? (int) $serviceId : 0;
        $db = Database::connection();

        if ($serviceId > 0) {
            $check = $db->prepare('SELECT s.id, s.title FROM services s JOIN service_categories c ON c.id=s.category_id WHERE s.id=? AND s.is_active=1 AND c.is_active=1 LIMIT 1');
            $check->bind_param('i', $serviceId);
            $check->execute();
            $selected = $check->get_result()->fetch_assoc();
            if (!$selected) {
                $serviceId = 0;
            } elseif ($data['requested_service'] === '') {
                $data['requested_service'] = $selected['title'];
            }
        }

        $source = trim((string) ($_POST['source'] ?? 'website'));
        if (!preg_match('/^[A-Za-z0-9._-]{1,100}$/', $source)) {
            $source = 'website';
        }
        $stmt = $db->prepare("INSERT INTO leads (full_name, company_name, phone, email, lead_type, service_id, requested_service, source, description, privacy_consented) VALUES (?, NULLIF(?, ''), ?, NULLIF(?, ''), ?, NULLIF(?, 0), NULLIF(?, ''), ?, NULLIF(?, ''), 1)");
        $stmt->bind_param(
            'sssssisss',
            $data['full_name'],
            $data['company_name'],
            $data['phone'],
            $data['email'],
            $leadType,
            $serviceId,
            $data['requested_service'],
            $source,
            $data['description']
        );
        $stmt->execute();
        flash('success', 'درخواست شما ثبت شد. برای هماهنگی با شما تماس می‌گیریم.');
        redirect_to('/#contact');
    }

    /** @return array<int,array<string,mixed>> */
    private function getPlans(\mysqli $db): array
    {
        $plans = $db->query('SELECT p.id, p.service_id, p.title, p.slug, p.audience, p.summary, p.is_featured, s.title AS service_title FROM plans p LEFT JOIN services s ON s.id=p.service_id WHERE p.is_published=1 ORDER BY p.display_order, p.id')->fetch_all(MYSQLI_ASSOC);
        if ($plans === []) {
            return [];
        }
        $ids = array_map(static fn ($p) => (int) $p['id'], $plans);
        $in = implode(',', $ids);
        $features = $db->query("SELECT plan_id, feature_text, is_included FROM plan_features WHERE plan_id IN ($in) ORDER BY display_order, id")->fetch_all(MYSQLI_ASSOC);
        $byPlan = [];
        foreach ($features as $feature) {
            $byPlan[(int) $feature['plan_id']][] = $feature;
        }
        foreach ($plans as &$plan) {
            $plan['features'] = $byPlan[(int) $plan['id']] ?? [];
            $plan['price_info'] = $this->publicPriceInfo($db, 'plan_id', (int) $plan['id']);
        }
        unset($plan);
        return $plans;
    }

    /** Only a verified official price with a source can be exposed as official. */
    private function publicPriceInfo(\mysqli $db, string $target, int $targetId): array
    {
        $column = in_array($target, ['service_id', 'plan_id'], true) ? $target : 'plan_id';
        $stmt = $db->prepare("SELECT pr.price_type, pr.price_component, pr.amount_min, pr.amount_max, pr.unit, pr.billing_period, pr.tariff_year, ps.title AS source_title, ps.source_url FROM price_records pr LEFT JOIN price_sources ps ON ps.id=pr.source_id WHERE pr.$column=? AND pr.is_visible=1 AND (pr.valid_from IS NULL OR pr.valid_from<=CURRENT_DATE()) AND (pr.valid_until IS NULL OR pr.valid_until>=CURRENT_DATE()) AND (pr.price_type<>'official' OR (pr.verification_status='verified' AND pr.source_id IS NOT NULL)) ORDER BY pr.updated_at DESC, pr.id DESC");
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if ($rows === []) {
            return ['label' => 'استعلام قیمت', 'type_label' => 'قیمت پس از نیازسنجی اعلام می‌شود', 'source_title' => null, 'source_url' => null, 'unit' => null, 'components' => []];
        }
        $typeLabels = [
            'official' => 'تعرفه رسمی تأییدشده',
            'company' => 'قیمت پیشنهادی شرکت',
            'negotiable' => 'قیمت توافقی',
            'estimate' => 'نیازمند برآورد',
        ];
        $componentLabels = ['setup' => 'هزینه راه‌اندازی', 'recurring' => 'هزینه دوره‌ای', 'custom' => 'هزینه'];
        $seen = [];
        $components = [];
        foreach ($rows as $price) {
            $component = (string) ($price['price_component'] ?? 'custom');
            $dedupeKey = $component . ':' . (string) $price['price_type'];
            if (isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;
            $label = 'استعلام قیمت';
            if (!in_array($price['price_type'], ['negotiable', 'estimate'], true) && $price['amount_min'] !== null) {
                $min = number_format((int) $price['amount_min']);
                if ($price['amount_max'] !== null && (int) $price['amount_max'] !== (int) $price['amount_min']) {
                    $label = $min . ' تا ' . number_format((int) $price['amount_max']) . ' تومان';
                } else {
                    $label = $min . ' تومان';
                }
            }
            $typeLabel = $typeLabels[$price['price_type']] ?? 'استعلام قیمت';
            if ($price['price_type'] === 'official' && $price['tariff_year']) {
                $typeLabel .= ' · سال ' . $price['tariff_year'];
            }
            $componentLabel = $componentLabels[$component] ?? 'هزینه';
            if ($component === 'recurring' && $price['billing_period']) {
                $componentLabel .= ' · ' . $price['billing_period'];
            }
            $components[] = [
                'label' => $label,
                'type_label' => $typeLabel,
                'component_label' => $componentLabel,
                'source_title' => $price['source_title'],
                'source_url' => $price['source_url'],
                'unit' => $price['unit'],
            ];
        }
        $first = $components[0] ?? ['label' => 'استعلام قیمت', 'type_label' => 'استعلام قیمت', 'source_title' => null, 'source_url' => null, 'unit' => null];
        return [
            'label' => count($components) > 1 ? 'تفکیک هزینه‌ها' : $first['label'],
            'type_label' => count($components) > 1 ? 'هزینه‌های راه‌اندازی و دوره‌ای جداگانه درج شده‌اند' : $first['type_label'],
            'source_title' => $first['source_title'],
            'source_url' => $first['source_url'],
            'unit' => $first['unit'],
            'components' => $components,
        ];
    }

    private function leadSource(): string
    {
        $source = trim((string) ($_GET['utm_source'] ?? 'website'));
        return preg_match('/^[A-Za-z0-9._-]{1,100}$/', $source) ? $source : 'website';
    }

    /** @return array<string,string> */
    private function getSettings(\mysqli $db): array
    {
        $rows = $db->query('SELECT setting_key, setting_value FROM site_settings')->fetch_all(MYSQLI_ASSOC);
        $values = [];
        foreach ($rows as $row) {
            $values[$row['setting_key']] = (string) $row['setting_value'];
        }
        return $values;
    }
}
