<?php $user = current_user(); ?>
<header class="admin-header">
  <div class="container header-inner">
    <a class="admin-brand" href="<?= e(site_url('/admin')) ?>"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 2.8 28.5 10v12L16 29.2 3.5 22V10L16 2.8Z" stroke="#65e1d7" stroke-width="1.8"/><path d="m16 8.5 7.5 4.3v6.4L16 23.5l-7.5-4.3v-6.4L16 8.5Z" stroke="#fff" stroke-width="1.4"/></svg></span><span>پنل مدیریت<small><?= e($user['name'] ?? 'مدیریت محتوا') ?></small></span></a>
    <?php if ($user): ?><nav class="admin-nav" aria-label="منوی مدیریت">
      <a href="<?= e(site_url('/admin')) ?>">داشبورد</a>
      <?php if (can_manage('leads')): ?><a href="<?= e(site_url('/admin/leads')) ?>">سرنخ‌ها</a><?php endif; ?>
      <?php if (can_manage('services')): ?><a href="<?= e(site_url('/admin/services')) ?>">خدمات</a><?php endif; ?>
      <?php if (can_manage('plans')): ?><a href="<?= e(site_url('/admin/plans')) ?>">پلن‌ها</a><?php endif; ?>
      <?php if (can_manage('prices')): ?><a href="<?= e(site_url('/admin/prices')) ?>">تعرفه‌ها</a><?php endif; ?>
      <?php if (can_manage('portfolio')): ?><a href="<?= e(site_url('/admin/portfolio')) ?>">نمونه‌کارها</a><?php endif; ?>
      <?php if (can_manage('settings')): ?><a href="<?= e(site_url('/admin/settings')) ?>">تنظیمات</a><?php endif; ?>
      <form action="<?= e(site_url('/admin/logout')) ?>" method="post" class="logout-form"><?= csrf_field() ?><button type="submit" class="btn btn-ghost-light">خروج</button></form>
    </nav><?php else: ?><a class="btn btn-ghost-light" href="<?= e(site_url('/')) ?>">بازگشت به سایت</a><?php endif; ?>
  </div>
</header>
