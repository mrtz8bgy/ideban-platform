<?php
$footerName = $settings['company_name'] ?? 'شبکه پردازان ایده‌بان الماس';
$footerPhone = $settings['phone'] ?? '09104927131';
$footerTel = preg_replace('/[^0-9+]/', '', $footerPhone) ?: '09104927131';
$footerSlogan = $settings['slogan'] ?? 'زیرساخت فناوری کسب‌وکار شما؛ از طراحی و توسعه تا استقرار، امنیت و پشتیبانی';
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-main">
      <div class="footer-brand">
        <a class="brand" href="<?= e(site_url('/')) ?>"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 2.8 28.5 10v12L16 29.2 3.5 22V10L16 2.8Z" stroke="#65e1d7" stroke-width="1.8"/><path d="m16 8.5 7.5 4.3v6.4L16 23.5l-7.5-4.3v-6.4L16 8.5Z" stroke="#fff" stroke-width="1.4"/></svg></span><span class="brand-text"><strong><?= e($footerName) ?></strong><small>IT SOLUTIONS · DEV · INFRA</small></span></a>
        <p><?= e($footerSlogan) ?></p>
      </div>
      <div class="footer-col"><strong>دسترسی سریع</strong><div class="footer-links"><a href="<?= e(site_url('/services')) ?>">خدمات فناوری اطلاعات</a><a href="<?= e(site_url('/pricing')) ?>">پلن‌ها و تعرفه‌ها</a><a href="<?= e(site_url('/portfolio')) ?>">نمونه‌کارها</a><a href="<?= e(site_url('/contact')) ?>">درخواست مشاوره</a></div></div>
      <div class="footer-col"><strong>ارتباط با ما</strong><div class="footer-links"><a class="footer-phone" href="tel:<?= e($footerTel) ?>" dir="ltr"><?= e($footerPhone) ?></a><span>خدمات حضوری با توجه به محدوده جغرافیایی و توافق ارائه می‌شود.</span><a href="<?= e(site_url('/admin/login')) ?>">ورود مدیریت</a></div></div>
    </div>
    <div class="footer-bottom"><span>© <?= e(date('Y')) ?> <?= e($footerName) ?> — تمامی حقوق محفوظ است.</span><span>فارسی · RTL · طراحی واکنش‌گرا</span></div>
  </div>
</footer>
