<?php
$headerPhone = $settings['phone'] ?? '09104927131';
$telPhone = preg_replace('/[^0-9+]/', '', $headerPhone) ?: '09104927131';
$companyName = $settings['company_name'] ?? 'شبکه پردازان ایده‌بان الماس';
?>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= e(site_url('/')) ?>" aria-label="صفحه اصلی شبکه پردازان ایده‌بان الماس">
      <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 2.8 28.5 10v12L16 29.2 3.5 22V10L16 2.8Z" stroke="#65e1d7" stroke-width="1.8"/><path d="m16 8.5 7.5 4.3v6.4L16 23.5l-7.5-4.3v-6.4L16 8.5Z" stroke="#fff" stroke-width="1.4"/><path d="M16 8.5v15M8.5 12.8 16 17l7.5-4.2" stroke="#65e1d7" stroke-width="1.2"/></svg></span>
      <span class="brand-text"><strong><?= e($companyName) ?></strong><small>IT SOLUTIONS · DEV · INFRA</small></span>
    </a>
    <nav class="main-nav" id="main-nav" aria-label="منوی اصلی">
      <a href="<?= e(site_url('/services')) ?>">خدمات</a>
      <a href="<?= e(site_url('/pricing')) ?>">پلن‌ها و تعرفه‌ها</a>
      <a href="<?= e(site_url('/portfolio')) ?>">نمونه‌کارها</a>
      <a href="<?= e(site_url('/#about')) ?>">درباره ما</a>
      <a href="<?= e(site_url('/contact')) ?>">تماس با ما</a>
    </nav>
    <div class="header-actions">
      <a class="phone-link" href="tel:<?= e($telPhone) ?>" dir="ltr"><?= e($headerPhone) ?></a>
      <a class="btn btn-dark" href="<?= e(site_url('/contact#contact')) ?>">درخواست مشاوره <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
    </div>
    <button class="menu-toggle" type="button" aria-label="باز کردن منو" aria-expanded="false" aria-controls="main-nav"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
  </div>
</header>
