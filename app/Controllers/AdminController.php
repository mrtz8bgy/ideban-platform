<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\RateLimiter;
use mysqli;
use Throwable;

final class AdminController
{
    public function loginForm(): void
    {
        if (current_user() !== null) {
            redirect_to('/admin');
        }
        render_view('admin/login', ['pageTitle' => 'ورود به پنل مدیریت', 'isAdmin' => true]);
    }

    public function loginPost(): void
    {
        verify_csrf();
        if (!RateLimiter::allow('admin_login', 8, 900)) {
            flash('error', 'تلاش‌های ورود بیش از حد مجاز است. کمی بعد دوباره امتحان کنید.');
            redirect_to('/admin/login');
        }

        $email = trim(strtolower((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 300) {
            flash('error', 'ایمیل یا گذرواژه نادرست است.');
            redirect_to('/admin/login');
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT u.id, u.full_name, u.email, u.password_hash, GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ",") AS roles FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.id LEFT JOIN roles r ON r.id=ur.role_id WHERE u.email=? AND u.is_active=1 GROUP BY u.id LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'ایمیل یا گذرواژه نادرست است.');
            redirect_to('/admin/login');
        }

        $roles = array_values(array_filter(explode(',', (string) ($user['roles'] ?? ''))));
        if ($roles === []) {
            flash('error', 'برای این حساب هیچ نقشی تعریف نشده است. با مدیر سامانه تماس بگیرید.');
            redirect_to('/admin/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
            'roles' => $roles,
        ];
        unset($_SESSION['_rate_limits']['admin_login']);
        $update = $db->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?');
        $userId = (int) $user['id'];
        $update->bind_param('i', $userId);
        $update->execute();
        write_audit('login', 'user', $userId, 'ورود موفق به پنل');
        redirect_to('/admin');
    }

    public function logout(): void
    {
        verify_csrf();
        write_audit('logout', 'user', (int) (current_user()['id'] ?? 0), 'خروج از پنل');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        redirect_to('/admin/login');
    }

    public function dashboard(): void
    {
        require_staff();
        $db = Database::connection();
        $canSeeLeads = can_manage('leads');
        $counts = [
            'new_leads' => $canSeeLeads ? (int) $db->query("SELECT COUNT(*) AS c FROM leads WHERE status='new'")->fetch_assoc()['c'] : 0,
            'open_leads' => $canSeeLeads ? (int) $db->query("SELECT COUNT(*) AS c FROM leads WHERE status NOT IN ('won','lost')")->fetch_assoc()['c'] : 0,
            'services' => (int) $db->query('SELECT COUNT(*) AS c FROM services WHERE is_active=1')->fetch_assoc()['c'],
            'published_projects' => (int) $db->query('SELECT COUNT(*) AS c FROM portfolio_projects WHERE is_published=1 AND publication_approved=1')->fetch_assoc()['c'],
        ];
        $recent = $canSeeLeads ? $db->query('SELECT id, full_name, company_name, phone, requested_service, status, created_at FROM leads ORDER BY created_at DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC) : [];
        render_view('admin/dashboard', [
            'pageTitle' => 'داشبورد مدیریت', 'isAdmin' => true, 'counts' => $counts, 'recent' => $recent,
        ]);
    }

    public function leads(): void
    {
        require_staff('leads');
        $db = Database::connection();
        $leads = $db->query('SELECT l.*, s.title AS service_title FROM leads l LEFT JOIN services s ON s.id=l.service_id ORDER BY l.created_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC);
        $noteRows = $db->query('SELECT n.lead_id, n.note, n.created_at, u.full_name AS author FROM lead_notes n LEFT JOIN users u ON u.id=n.user_id ORDER BY n.created_at DESC LIMIT 500')->fetch_all(MYSQLI_ASSOC);
        $notesByLead = [];
        foreach ($noteRows as $noteRow) { $notesByLead[(int) $noteRow['lead_id']][] = $noteRow; }
        foreach ($leads as &$lead) { $lead['notes'] = $notesByLead[(int) $lead['id']] ?? []; }
        unset($lead);
        render_view('admin/leads', ['pageTitle' => 'سرنخ‌های فروش', 'isAdmin' => true, 'leads' => $leads]);
    }

    public function updateLead(): void
    {
        require_staff('leads');
        verify_csrf();
        $id = filter_var($_POST['lead_id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string) ($_POST['status'] ?? '');
        $note = trim((string) ($_POST['note'] ?? ''));
        $followUp = trim((string) ($_POST['next_follow_up_at'] ?? ''));
        $allowed = ['new', 'contacted', 'needs_assessment', 'proposal_sent', 'negotiation', 'won', 'lost'];
        if (!$id || !in_array($status, $allowed, true) || strlen($note) > 4000) {
            flash('error', 'اطلاعات پیگیری معتبر نیست.');
            redirect_to('/admin/leads');
        }
        if ($followUp !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $followUp)) {
            flash('error', 'زمان پیگیری معتبر نیست.');
            redirect_to('/admin/leads');
        }
        $followUp = $followUp === '' ? '' : str_replace('T', ' ', $followUp) . ':00';
        $db = Database::connection();
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("UPDATE leads SET status=?, next_follow_up_at=NULLIF(?, '') WHERE id=?");
            $stmt->bind_param('ssi', $status, $followUp, $id);
            $stmt->execute();
            if ($note !== '') {
                $userId = (int) current_user()['id'];
                $noteStmt = $db->prepare('INSERT INTO lead_notes (lead_id, user_id, note) VALUES (?, ?, ?)');
                $noteStmt->bind_param('iis', $id, $userId, $note);
                $noteStmt->execute();
            }
            $db->commit();
            write_audit('lead_updated', 'lead', (int) $id, 'تغییر مرحله و ثبت پیگیری');
            flash('success', 'پیگیری سرنخ ذخیره شد.');
        } catch (Throwable $e) {
            $db->rollback();
            error_log('Lead update failed: ' . $e->getMessage());
            flash('error', 'ذخیره پیگیری انجام نشد.');
        }
        redirect_to('/admin/leads');
    }

    public function services(): void
    {
        require_staff('services');
        $db = Database::connection();
        $services = $db->query('SELECT s.*, c.title AS category_title FROM services s JOIN service_categories c ON c.id=s.category_id ORDER BY c.display_order, s.display_order, s.id')->fetch_all(MYSQLI_ASSOC);
        $categories = $db->query('SELECT id, title FROM service_categories WHERE is_active=1 ORDER BY display_order, title')->fetch_all(MYSQLI_ASSOC);
        $allCategories = $db->query('SELECT * FROM service_categories ORDER BY display_order, id')->fetch_all(MYSQLI_ASSOC);
        render_view('admin/services', ['pageTitle' => 'مدیریت خدمات', 'isAdmin' => true, 'services' => $services, 'categories' => $categories, 'allCategories' => $allCategories]);
    }

    public function saveCategory(): void
    {
        require_staff('services');
        verify_csrf();
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $this->slug((string) ($_POST['slug'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $order = max(0, min(999, (int) ($_POST['display_order'] ?? 0)));
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($title === '' || \text_length($title) > 150 || $slug === '' || \text_length($description) > 500) {
            flash('error', 'عنوان، شناسه لاتین و توضیحات دسته‌بندی را بررسی کنید.');
            redirect_to('/admin/services');
        }
        $db = Database::connection();
        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE service_categories SET title=?, slug=?, description=NULLIF(?,''), display_order=?, is_active=? WHERE id=?");
                $stmt->bind_param('sssiii', $title, $slug, $description, $order, $active, $id);
                $stmt->execute();
                write_audit('service_category_updated', 'service_category', $id, $title);
            } else {
                $stmt = $db->prepare("INSERT INTO service_categories (title, slug, description, display_order, is_active) VALUES (?, ?, NULLIF(?,''), ?, ?)");
                $stmt->bind_param('sssii', $title, $slug, $description, $order, $active);
                $stmt->execute();
                write_audit('service_category_created', 'service_category', (int) $db->insert_id, $title);
            }
            flash('success', 'دسته‌بندی ذخیره شد.');
        } catch (Throwable $e) {
            error_log('Service category save failed: ' . $e->getMessage());
            flash('error', 'ذخیره دسته‌بندی انجام نشد؛ شناسه لاتین باید یکتا باشد.');
        }
        redirect_to('/admin/services');
    }

    public function saveService(): void
    {
        require_staff('services');
        verify_csrf();
        $db = Database::connection();
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $categoryId = max(0, (int) ($_POST['category_id'] ?? 0));
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $this->slug((string) ($_POST['slug'] ?? ''));
        $short = trim((string) ($_POST['short_description'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $includes = trim((string) ($_POST['scope_includes'] ?? ''));
        $excludes = trim((string) ($_POST['scope_excludes'] ?? ''));
        $delivery = trim((string) ($_POST['delivery_note'] ?? ''));
        $active = isset($_POST['is_active']) ? 1 : 0;
        $order = max(0, min(999, (int) ($_POST['display_order'] ?? 0)));

        if ($title === '' || \text_length($title) > 180 || $slug === '' || $short === '' || \text_length($short) > 600 || \text_length($description) > 12000 || \text_length($includes) > 4000 || \text_length($excludes) > 4000 || \text_length($delivery) > 500 || $categoryId < 1) {
            flash('error', 'عنوان، شناسه لاتین، دسته‌بندی و معرفی کوتاه را کامل کنید.');
            redirect_to('/admin/services');
        }
        $categoryCheck = $db->prepare('SELECT id FROM service_categories WHERE id=? AND is_active=1');
        $categoryCheck->bind_param('i', $categoryId);
        $categoryCheck->execute();
        if (!$categoryCheck->get_result()->fetch_assoc()) {
            flash('error', 'دسته‌بندی فعال و معتبری انتخاب کنید.');
            redirect_to('/admin/services');
        }
        try {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE services SET category_id=?, title=?, slug=?, short_description=?, description=?, scope_includes=?, scope_excludes=?, delivery_note=?, is_active=?, display_order=? WHERE id=?');
                $stmt->bind_param('isssssssiii', $categoryId, $title, $slug, $short, $description, $includes, $excludes, $delivery, $active, $order, $id);
                $stmt->execute();
                write_audit('service_updated', 'service', $id, $title);
            } else {
                $stmt = $db->prepare('INSERT INTO services (category_id, title, slug, short_description, description, scope_includes, scope_excludes, delivery_note, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('isssssssii', $categoryId, $title, $slug, $short, $description, $includes, $excludes, $delivery, $active, $order);
                $stmt->execute();
                write_audit('service_created', 'service', (int) $db->insert_id, $title);
            }
            flash('success', 'خدمت ذخیره شد.');
        } catch (Throwable $e) {
            error_log('Service save failed: ' . $e->getMessage());
            flash('error', 'ذخیره خدمت انجام نشد؛ شناسه لاتین باید یکتا باشد.');
        }
        redirect_to('/admin/services');
    }

    public function plans(): void
    {
        require_staff('plans');
        $db = Database::connection();
        $plans = $db->query('SELECT p.*, s.title AS service_title FROM plans p LEFT JOIN services s ON s.id=p.service_id ORDER BY p.display_order, p.id')->fetch_all(MYSQLI_ASSOC);
        $features = $db->query('SELECT plan_id, feature_text FROM plan_features ORDER BY display_order, id')->fetch_all(MYSQLI_ASSOC);
        $grouped = [];
        foreach ($features as $feature) {
            $grouped[(int) $feature['plan_id']][] = $feature['feature_text'];
        }
        foreach ($plans as &$plan) {
            $plan['features_text'] = implode("\n", $grouped[(int) $plan['id']] ?? []);
        }
        unset($plan);
        $services = $db->query('SELECT id, title FROM services WHERE is_active=1 ORDER BY title')->fetch_all(MYSQLI_ASSOC);
        render_view('admin/plans', ['pageTitle' => 'مدیریت پلن‌ها', 'isAdmin' => true, 'plans' => $plans, 'services' => $services]);
    }

    public function savePlan(): void
    {
        require_staff('plans');
        verify_csrf();
        $db = Database::connection();
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $serviceId = max(0, (int) ($_POST['service_id'] ?? 0));
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $this->slug((string) ($_POST['slug'] ?? ''));
        $audience = trim((string) ($_POST['audience'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $featureLines = preg_split('/\r\n|\r|\n/', (string) ($_POST['features'] ?? '')) ?: [];
        $featureLines = array_values(array_filter(array_map('trim', $featureLines), static fn ($line) => $line !== ''));
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $published = isset($_POST['is_published']) ? 1 : 0;
        $order = max(0, min(999, (int) ($_POST['display_order'] ?? 0)));
        $oversizedFeature = array_filter($featureLines, static fn ($line) => \text_length($line) > 300);
        if ($title === '' || \text_length($title) > 150 || $slug === '' || $audience === '' || \text_length($audience) > 250 || $summary === '' || \text_length($summary) > 600 || count($featureLines) > 20 || $oversizedFeature !== []) {
            flash('error', 'اطلاعات پلن را کامل و معتبر وارد کنید.');
            redirect_to('/admin/plans');
        }
        if ($serviceId > 0) {
            $check = $db->prepare('SELECT id FROM services WHERE id=?');
            $check->bind_param('i', $serviceId);
            $check->execute();
            if (!$check->get_result()->fetch_assoc()) {
                flash('error', 'خدمت مرتبط معتبر نیست.');
                redirect_to('/admin/plans');
            }
        }
        try {
            $db->begin_transaction();
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE plans SET service_id=NULLIF(?,0), title=?, slug=?, audience=?, summary=?, is_featured=?, is_published=?, display_order=? WHERE id=?');
                $stmt->bind_param('issssiiii', $serviceId, $title, $slug, $audience, $summary, $featured, $published, $order, $id);
                $stmt->execute();
                $planId = $id;
                $db->query('DELETE FROM plan_features WHERE plan_id=' . (int) $planId);
                write_audit('plan_updated', 'plan', $planId, $title);
            } else {
                $stmt = $db->prepare('INSERT INTO plans (service_id, title, slug, audience, summary, is_featured, is_published, display_order) VALUES (NULLIF(?,0), ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('issssiii', $serviceId, $title, $slug, $audience, $summary, $featured, $published, $order);
                $stmt->execute();
                $planId = (int) $db->insert_id;
                write_audit('plan_created', 'plan', $planId, $title);
            }
            if ($featureLines !== []) {
                $featureStmt = $db->prepare('INSERT INTO plan_features (plan_id, feature_text, display_order) VALUES (?, ?, ?)');
                foreach ($featureLines as $index => $featureText) {
                    $sort = $index + 1;
                    $featureStmt->bind_param('isi', $planId, $featureText, $sort);
                    $featureStmt->execute();
                }
            }
            $db->commit();
            flash('success', 'پلن و امکانات آن ذخیره شد. قیمت این پلن از بخش تعرفه‌ها مدیریت می‌شود.');
        } catch (Throwable $e) {
            $db->rollback();
            error_log('Plan save failed: ' . $e->getMessage());
            flash('error', 'ذخیره پلن انجام نشد؛ شناسه لاتین باید یکتا باشد.');
        }
        redirect_to('/admin/plans');
    }

    public function prices(): void
    {
        require_staff('prices');
        $db = Database::connection();
        $prices = $db->query('SELECT pr.*, s.title AS service_title, p.title AS plan_title, ps.title AS source_title, ps.source_url AS source_url FROM price_records pr LEFT JOIN services s ON s.id=pr.service_id LEFT JOIN plans p ON p.id=pr.plan_id LEFT JOIN price_sources ps ON ps.id=pr.source_id ORDER BY pr.updated_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC);
        $services = $db->query('SELECT id, title FROM services ORDER BY title')->fetch_all(MYSQLI_ASSOC);
        $plans = $db->query('SELECT id, title FROM plans ORDER BY title')->fetch_all(MYSQLI_ASSOC);
        render_view('admin/prices', ['pageTitle' => 'تعرفه‌ها و منابع قیمت', 'isAdmin' => true, 'prices' => $prices, 'services' => $services, 'plans' => $plans]);
    }

    public function savePrice(): void
    {
        require_staff('prices');
        verify_csrf();
        $serviceId = max(0, (int) ($_POST['service_id'] ?? 0));
        $planId = max(0, (int) ($_POST['plan_id'] ?? 0));
        $type = (string) ($_POST['price_type'] ?? 'estimate');
        $component = (string) ($_POST['price_component'] ?? 'custom');
        $unit = trim((string) ($_POST['unit'] ?? 'پروژه'));
        $billingPeriod = trim((string) ($_POST['billing_period'] ?? ''));
        $minRaw = trim((string) ($_POST['amount_min'] ?? ''));
        $maxRaw = trim((string) ($_POST['amount_max'] ?? ''));
        $min = $this->amountOrNull($minRaw);
        $max = $this->amountOrNull($maxRaw);
        $year = max(0, min(3000, (int) ($_POST['tariff_year'] ?? 0)));
        $verification = (string) ($_POST['verification_status'] ?? 'pending');
        $sourceTitle = trim((string) ($_POST['source_title'] ?? ''));
        $sourceUrl = trim((string) ($_POST['source_url'] ?? ''));
        $validFrom = trim((string) ($_POST['valid_from'] ?? ''));
        $validUntil = trim((string) ($_POST['valid_until'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $visible = isset($_POST['is_visible']) ? 1 : 0;
        $allowedTypes = ['official', 'company', 'negotiable', 'estimate'];
        $allowedVerification = ['pending', 'verified', 'rejected'];

        if ($serviceId < 1 && $planId < 1) {
            flash('error', 'قیمت باید به یک خدمت یا پلن متصل باشد.');
            redirect_to('/admin/prices');
        }
        if (!in_array($type, $allowedTypes, true) || !in_array($component, ['setup', 'recurring', 'custom'], true) || !in_array($verification, $allowedVerification, true) || $unit === '' || \text_length($unit) > 80 || \text_length($billingPeriod) > 40 || \text_length($sourceTitle) > 220 || \text_length($sourceUrl) > 1000 || \text_length($notes) > 5000) {
            flash('error', 'نوع یا واحد قیمت معتبر نیست.');
            redirect_to('/admin/prices');
        }
        if (($minRaw !== '' && $min === null) || ($maxRaw !== '' && $max === null)) {
            flash('error', 'مبلغ را با عدد صحیح و بدون علامت وارد کنید.');
            redirect_to('/admin/prices');
        }
        if ($min !== null && $max !== null && $max < $min) {
            flash('error', 'حداکثر مبلغ نمی‌تواند کمتر از حداقل باشد.');
            redirect_to('/admin/prices');
        }
        if ($type === 'official' && $verification === 'verified') {
            if ($sourceTitle === '' || !filter_var($sourceUrl, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $sourceUrl)) {
                flash('error', 'برای انتشار تعرفه رسمیِ تأییدشده، عنوان و لینک منبع معتبر لازم است.');
                redirect_to('/admin/prices');
            }
            if ($year < 1300 || $year > 3000) {
                flash('error', 'سال تعرفه رسمی را وارد کنید.');
                redirect_to('/admin/prices');
            }
        }
        if (($validFrom !== '' && !$this->validDate($validFrom)) || ($validUntil !== '' && !$this->validDate($validUntil))) {
            flash('error', 'بازه اعتبار قیمت معتبر نیست.');
            redirect_to('/admin/prices');
        }
        if ($validFrom !== '' && $validUntil !== '' && $validUntil < $validFrom) {
            flash('error', 'پایان اعتبار نمی‌تواند پیش از آغاز اعتبار باشد.');
            redirect_to('/admin/prices');
        }

        $db = Database::connection();
        $sourceId = 0;
        try {
            $db->begin_transaction();
            if ($sourceTitle !== '' && $sourceUrl !== '' && filter_var($sourceUrl, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $sourceUrl)) {
                $source = $db->prepare('INSERT INTO price_sources (title, source_url) VALUES (?, ?)');
                $source->bind_param('ss', $sourceTitle, $sourceUrl);
                $source->execute();
                $sourceId = (int) $db->insert_id;
            }
            $validFrom = $this->validDate($validFrom) ? $validFrom : '';
            $validUntil = $this->validDate($validUntil) ? $validUntil : '';
            $minValue = $min ?? 0;
            $maxValue = $max ?? 0;
            $yearValue = $year > 0 ? $year : 0;
            $stmt = $db->prepare("INSERT INTO price_records (service_id, plan_id, price_type, price_component, unit, billing_period, amount_min, amount_max, tariff_year, source_id, verification_status, valid_from, valid_until, notes, is_visible) VALUES (NULLIF(?,0), NULLIF(?,0), ?, ?, ?, NULLIF(?,''), NULLIF(?,0), NULLIF(?,0), NULLIF(?,0), NULLIF(?,0), ?, NULLIF(?,''), NULLIF(?,''), ?, ?)");
            $stmt->bind_param('iissssiiiissssi', $serviceId, $planId, $type, $component, $unit, $billingPeriod, $minValue, $maxValue, $yearValue, $sourceId, $verification, $validFrom, $validUntil, $notes, $visible);
            $stmt->execute();
            write_audit('price_created', 'price', (int) $db->insert_id, $type . ' / ' . $unit);
            $db->commit();
            flash('success', 'رکورد تعرفه ذخیره شد. تعرفه رسمی فقط در صورت تأیید و داشتن منبع نمایش داده می‌شود.');
        } catch (Throwable $e) {
            $db->rollback();
            error_log('Price save failed: ' . $e->getMessage());
            flash('error', 'ذخیره تعرفه انجام نشد.');
        }
        redirect_to('/admin/prices');
    }

    public function hidePrice(): void
    {
        require_staff('prices');
        verify_csrf();
        $id = filter_var($_POST['price_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            flash('error', 'شناسه تعرفه معتبر نیست.');
            redirect_to('/admin/prices');
        }
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE price_records SET is_visible=0 WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        write_audit('price_hidden', 'price', (int) $id, 'مخفی‌کردن رکورد تعرفه');
        flash('success', 'رکورد تعرفه از نمایش عمومی خارج شد.');
        redirect_to('/admin/prices');
    }

    public function portfolio(): void
    {
        require_staff('portfolio');
        $db = Database::connection();
        $projects = $db->query('SELECT * FROM portfolio_projects ORDER BY updated_at DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
        $mediaRows = $db->query('SELECT project_id, media_type, media_url, alt_text FROM project_media ORDER BY display_order, id')->fetch_all(MYSQLI_ASSOC);
        $mediaByProject = [];
        foreach ($mediaRows as $mediaRow) { $mediaByProject[(int) $mediaRow['project_id']][] = $mediaRow['media_type'] . '|' . $mediaRow['media_url'] . '|' . ($mediaRow['alt_text'] ?? ''); }
        foreach ($projects as &$project) { $project['media_items'] = implode("\n", $mediaByProject[(int) $project['id']] ?? []); }
        unset($project);
        render_view('admin/portfolio', ['pageTitle' => 'مدیریت نمونه‌کارها', 'isAdmin' => true, 'projects' => $projects]);
    }

    public function saveProject(): void
    {
        require_staff('portfolio');
        verify_csrf();
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $this->slug((string) ($_POST['slug'] ?? ''));
        $client = trim((string) ($_POST['client_label'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $problem = trim((string) ($_POST['problem'] ?? ''));
        $solution = trim((string) ($_POST['solution'] ?? ''));
        $technologies = trim((string) ($_POST['technologies'] ?? ''));
        $result = trim((string) ($_POST['measurable_result'] ?? ''));
        $publicUrl = trim((string) ($_POST['public_url'] ?? ''));
        $executedOn = trim((string) ($_POST['executed_on'] ?? ''));
        $mediaInput = trim((string) ($_POST['media_items'] ?? ''));
        $approved = isset($_POST['publication_approved']) ? 1 : 0;
        $published = isset($_POST['is_published']) && $approved ? 1 : 0;
        $order = max(0, min(999, (int) ($_POST['display_order'] ?? 0)));
        if ($title === '' || \text_length($title) > 200 || $slug === '' || \text_length($client) > 180 || \text_length($category) > 120 || \text_length($problem) > 12000 || \text_length($solution) > 12000 || \text_length($technologies) > 800 || \text_length($result) > 4000 || \text_length($publicUrl) > 1000 || \text_length($mediaInput) > 8000) {
            flash('error', 'عنوان، شناسه لاتین یا طول اطلاعات نمونه‌کار معتبر نیست.');
            redirect_to('/admin/portfolio');
        }
        if ($publicUrl !== '' && (!filter_var($publicUrl, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $publicUrl))) {
            flash('error', 'لینک پروژه باید یک نشانی HTTP یا HTTPS معتبر باشد.');
            redirect_to('/admin/portfolio');
        }
        if ($executedOn !== '' && !$this->validDate($executedOn)) {
            flash('error', 'تاریخ اجرای پروژه معتبر نیست.');
            redirect_to('/admin/portfolio');
        }
        $mediaRows = [];
        $mediaLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $mediaInput) ?: []), static fn ($line) => $line !== ''));
        if (count($mediaLines) > 20) {
            flash('error', 'حداکثر ۲۰ پیوند رسانه برای هر پروژه ثبت کنید.');
            redirect_to('/admin/portfolio');
        }
        foreach ($mediaLines as $line) {
            $parts = array_map('trim', explode('|', $line, 3));
            $mediaType = $parts[0] ?? '';
            $mediaUrl = $parts[1] ?? '';
            $altText = $parts[2] ?? '';
            if (!in_array($mediaType, ['image', 'video'], true) || \text_length($mediaUrl) > 1000 || !filter_var($mediaUrl, FILTER_VALIDATE_URL) || !preg_match('#^https://#i', $mediaUrl) || \text_length($altText) > 250) {
                flash('error', 'پیوند رسانه معتبر نیست؛ هر خط را به شکل image|https://…|توضیح یا video|https://…|توضیح وارد کنید.');
                redirect_to('/admin/portfolio');
            }
            $mediaRows[] = ['type' => $mediaType, 'url' => $mediaUrl, 'alt' => $altText];
        }
        $executedOn = $this->validDate($executedOn) ? $executedOn : '';
        $db = Database::connection();
        try {
            $db->begin_transaction();
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE portfolio_projects SET title=?, slug=?, client_label=NULLIF(?,''), category=NULLIF(?,''), problem=NULLIF(?,''), solution=NULLIF(?,''), technologies=NULLIF(?,''), measurable_result=NULLIF(?,''), public_url=NULLIF(?,''), executed_on=NULLIF(?,''), publication_approved=?, is_published=?, display_order=? WHERE id=?");
                $stmt->bind_param('ssssssssssiiii', $title, $slug, $client, $category, $problem, $solution, $technologies, $result, $publicUrl, $executedOn, $approved, $published, $order, $id);
                $stmt->execute();
                $projectId = $id;
            } else {
                $stmt = $db->prepare("INSERT INTO portfolio_projects (title, slug, client_label, category, problem, solution, technologies, measurable_result, public_url, executed_on, publication_approved, is_published, display_order) VALUES (?, ?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?, ?)");
                $stmt->bind_param('ssssssssssiii', $title, $slug, $client, $category, $problem, $solution, $technologies, $result, $publicUrl, $executedOn, $approved, $published, $order);
                $stmt->execute();
                $projectId = (int) $db->insert_id;
            }
            $deleteMedia = $db->prepare('DELETE FROM project_media WHERE project_id=?');
            $deleteMedia->bind_param('i', $projectId);
            $deleteMedia->execute();
            if ($mediaRows !== []) {
                $mediaStmt = $db->prepare("INSERT INTO project_media (project_id, media_type, media_url, alt_text, display_order) VALUES (?, ?, ?, NULLIF(?,''), ?)");
                foreach ($mediaRows as $index => $media) {
                    $sort = $index + 1;
                    $mediaStmt->bind_param('isssi', $projectId, $media['type'], $media['url'], $media['alt'], $sort);
                    $mediaStmt->execute();
                }
            }
            $db->commit();
            write_audit($id > 0 ? 'project_updated' : 'project_created', 'portfolio_project', $projectId, $title);
            flash('success', 'نمونه‌کار ذخیره شد. برای انتشار عمومی، مجوز انتشار و وضعیت انتشار هر دو باید فعال باشند.');
        } catch (Throwable $e) {
            $db->rollback();
            error_log('Portfolio save failed: ' . $e->getMessage());
            flash('error', 'ذخیره نمونه‌کار یا رسانه‌های آن انجام نشد؛ شناسه لاتین باید یکتا باشد.');
        }
        redirect_to('/admin/portfolio');
    }

    public function settings(): void
    {
        require_staff('settings');
        $db = Database::connection();
        $rows = $db->query('SELECT setting_key, setting_value FROM site_settings')->fetch_all(MYSQLI_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = (string) $row['setting_value'];
        }
        render_view('admin/settings', ['pageTitle' => 'اطلاعات شرکت', 'isAdmin' => true, 'settings' => $settings]);
    }

    public function saveSettings(): void
    {
        require_staff('settings');
        verify_csrf();
        $companyName = trim((string) ($_POST['company_name'] ?? ''));
        $phoneInput = strtr(trim((string) ($_POST['phone'] ?? '')), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $phoneInput) ?? '';
        $phoneDigits = preg_replace('/[^0-9]/', '', $normalizedPhone) ?? '';
        if ($companyName === '' || \text_length($companyName) > 180 || strlen($phoneDigits) < 8 || strlen($phoneDigits) > 18) {
            flash('error', 'نام شرکت و شماره تماس معتبر را وارد کنید.');
            redirect_to('/admin/settings');
        }
        $_POST['phone'] = $normalizedPhone;
        $allowed = ['company_name', 'founder_name', 'founder_title', 'phone', 'slogan', 'about'];
        $db = Database::connection();
        try {
            $db->begin_transaction();
            $stmt = $db->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            foreach ($allowed as $key) {
                $value = trim((string) ($_POST[$key] ?? ''));
                if (\text_length($value) > 2000) {
                    throw new \InvalidArgumentException('اطلاعات بیش از حد طولانی است.');
                }
                $stmt->bind_param('ss', $key, $value);
                $stmt->execute();
            }
            $db->commit();
            write_audit('settings_updated', 'site_settings', null, 'اطلاعات عمومی شرکت');
            flash('success', 'اطلاعات شرکت ذخیره شد.');
        } catch (Throwable $e) {
            $db->rollback();
            error_log('Settings update failed: ' . $e->getMessage());
            flash('error', 'ذخیره اطلاعات انجام نشد.');
        }
        redirect_to('/admin/settings');
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        return strlen($value) <= 180 && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) ? $value : '';
    }

    private function amountOrNull(mixed $value): ?int
    {
        $value = strtr(trim((string) $value), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',','=>'','٬'=>'',' '=>'']);
        if ($value === '') {
            return null;
        }
        if (!ctype_digit($value) || strlen($value) > 15) {
            return null;
        }
        return (int) $value;
    }

    private function validDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}
