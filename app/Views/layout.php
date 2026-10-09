<?php
$flashMessage = pull_flash();
$settings = $settings ?? [];
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$canonicalUrl = site_url($currentPath);
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($metaDescription ?? '') ?>">
  <meta name="robots" content="<?= $isAdmin ? 'noindex,nofollow' : 'index,follow' ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <meta property="og:locale" content="fa_IR">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($pageTitle ?? 'شبکه پردازان ایده‌بان الماس') ?>">
  <meta property="og:description" content="<?= e($metaDescription ?? '') ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta name="theme-color" content="#081c2d">
  <title><?= e($pageTitle ?? 'شبکه پردازان ایده‌بان الماس') ?></title>
  <link rel="stylesheet" href="<?= e(site_url('/assets/site.css')) ?>">
  <script src="<?= e(site_url('/assets/site.js')) ?>" defer></script>
</head>
<body class="<?= $isAdmin ? 'admin-body' : '' ?>">
<a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>
<?php if ($isAdmin): ?>
  <?php require APP_ROOT . '/app/Views/partials/admin-header.php'; ?>
<?php else: ?>
  <?php require APP_ROOT . '/app/Views/partials/header.php'; ?>
<?php endif; ?>
<?php if ($flashMessage): ?>
  <div class="alert <?= $flashMessage['type'] === 'success' ? 'alert-success' : 'alert-error' ?>" role="status"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
<main id="main-content">
<?= $content ?>
</main>
<?php if (!$isAdmin): ?>
  <?php require APP_ROOT . '/app/Views/partials/footer.php'; ?>
<?php endif; ?>
</body>
</html>
