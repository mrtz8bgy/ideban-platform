<?php
$company = $settings['company_name'] ?? 'شبکه پردازان ایده‌بان الماس';
$founder = $settings['founder_name'] ?? 'مرتضی بهنامی';
$founderTitle = $settings['founder_title'] ?? 'بنیان‌گذار و مشاور فناوری کسب‌وکار';
$phone = $settings['phone'] ?? '09104927131';
$tel = preg_replace('/[^0-9+]/', '', $phone) ?: '09104927131';
$slogan = $settings['slogan'] ?? 'زیرساخت فناوری کسب‌وکار شما؛ از طراحی و توسعه تا استقرار، امنیت و پشتیبانی';
$serviceIcons = [
  'web-software' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="15" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 8h18M8 4v4m-3 3h6m-6 3h10m-10 3h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
  'devops-infrastructure' => '<svg viewBox="0 0 24 24" fill="none"><rect x="4" y="3" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/><rect x="4" y="14" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 6.5h.01M8 17.5h.01M12 6.5h4M12 17.5h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
  'it-support' => '<svg viewBox="0 0 24 24" fill="none"><path d="M4 13v-1a8 8 0 0 1 16 0v1" stroke="currentColor" stroke-width="1.7"/><rect x="3" y="12" width="4" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/><rect x="17" y="12" width="4" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M17 19c-.5 1.4-2 2-4 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
  'network' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5" r="2.5" stroke="currentColor" stroke-width="1.7"/><circle cx="5" cy="19" r="2.5" stroke="currentColor" stroke-width="1.7"/><circle cx="19" cy="19" r="2.5" stroke="currentColor" stroke-width="1.7"/><path d="m10.8 7.2-4.5 9.6m6.9-9.6 4.5 9.6M7.5 19h9" stroke="currentColor" stroke-width="1.5"/></svg>',
  'security' => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3 20 6v5.2c0 4.7-3.3 8.1-8 9.8-4.7-1.7-8-5.1-8-9.8V6l8-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m8.5 12 2.2 2.2 4.8-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
];
?>
<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-copy">
        <div class="eyebrow"><span class="dot"></span> فناوریِ هم‌مسیر با کسب‌وکار شما</div>
        <h1>زیرساختی که<br><span>رشد کسب‌وکار</span> را ممکن می‌کند</h1>
        <p class="hero-lead"><?= e($slogan) ?>. از طراحی یک وب‌سایت تا استقرار، امنیت و پشتیبانیِ راهکارهای سازمانی.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= e(site_url('/contact#contact')) ?>">درخواست مشاوره <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          <a class="btn btn-ghost-light" href="<?= e(site_url('/services')) ?>">مشاهده خدمات <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="hero-note"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m4 10 3.5 3.5L16 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg> محدوده، زمان و هزینه هر همکاری پیش از شروع شفاف می‌شود.</div>
      </div>
      <div class="hero-art" aria-label="نمایش تصویری لایه‌های راهکار فناوری">
        <div class="orbit"></div>
        <div class="core-card">
          <div class="core-top"><span>نقشه راه فناوری کسب‌وکار</span><span class="live-pill">یکپارچه و قابل توسعه</span></div>
          <div class="core-title">از ایده تا عملیات پایدار</div>
          <div class="core-sub">راهکار متناسب با نیاز واقعی، نه پیچیدگی اضافه</div>
          <div class="system-list">
            <div class="system-row"><span class="system-icon"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="15" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M3 8h18" stroke="currentColor" stroke-width="1.5"/></svg></span><span class="system-info"><strong>طراحی و توسعه</strong><small>وب‌سایت و نرم‌افزار</small></span><span class="system-state">01</span></div>
            <div class="system-row"><span class="system-icon"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="3" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/><rect x="4" y="14" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.7"/></svg></span><span class="system-info"><strong>استقرار و زیرساخت</strong><small>Linux · Docker · CI/CD</small></span><span class="system-state">02</span></div>
            <div class="system-row"><span class="system-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 20 6v5c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-3Z" stroke="currentColor" stroke-width="1.7"/></svg></span><span class="system-info"><strong>امنیت و پشتیبانی</strong><small>نگهداری و پاسخ‌گویی</small></span><span class="system-state">03</span></div>
          </div>
        </div>
        <div class="floating-tag tag-one"><svg viewBox="0 0 24 24" fill="none"><path d="m4 12 5 5L20 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> محدوده همکاری شفاف</div>
        <div class="floating-tag tag-two"><svg viewBox="0 0 24 24" fill="none"><path d="M4 18V6m0 12h16M8 14l3-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> متناسب با نیاز سازمان</div>
      </div>
    </div>
    <div class="hero-bottom">
      <div class="hero-capability"><svg viewBox="0 0 20 20" fill="none"><path d="M3 5h14v10H3zM6 8h8M6 11h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg> وب‌سایت و نرم‌افزار</div>
      <div class="hero-capability"><svg viewBox="0 0 20 20" fill="none"><rect x="4" y="2.5" width="12" height="6" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="4" y="11.5" width="12" height="6" rx="1.5" stroke="currentColor" stroke-width="1.4"/></svg> سرور و DevOps</div>
      <div class="hero-capability"><svg viewBox="0 0 20 20" fill="none"><path d="M10 2.5 17 5v4.5c0 4-2.8 6.8-7 8-4.2-1.2-7-4-7-8V5l7-2.5Z" stroke="currentColor" stroke-width="1.4"/></svg> امنیت و پشتیبانی IT</div>
    </div>
  </div>
</section>

<section class="section" id="services">
  <div class="container">
    <div class="section-heading"><div><div class="section-kicker">خدمات فناوری اطلاعات</div><h2>از ساخت محصول تا پایداری زیرساخت</h2></div><p>خدمات را متناسب با مسئله کسب‌وکار تعریف می‌کنیم؛ محدوده اجرا و اقلام تحویلی پیش از شروع، در پیشنهاد و پیش‌فاکتور مشخص می‌شوند.</p></div>
    <div class="services-grid">
      <?php foreach (array_slice($services, 0, 6) as $index => $service): ?>
        <?php $icon = $serviceIcons[(string) ($service['category_slug'] ?? '')] ?? $serviceIcons['web-software']; ?>
        <article class="service-card"><div class="service-card-top"><span class="service-icon" aria-hidden="true"><?= $icon ?></span><span class="service-number">0<?= (int) ($index + 1) ?></span></div><h3><?= e($service['title']) ?></h3><p><?= e($service['short_description']) ?></p><a class="service-link" href="<?= e(site_url('/services/' . $service['slug'])) ?>">جزئیات خدمت <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a></article>
      <?php endforeach; ?>
      <?php if ($services === []): ?><div class="empty-state">خدمات هنوز در پنل مدیریت ثبت نشده‌اند.</div><?php endif; ?>
    </div>
    <div class="services-more"><a class="text-link" href="<?= e(site_url('/services')) ?>">مشاهده همه خدمات <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div>
  </div>
</section>

<section class="section section-soft" id="about">
  <div class="container two-col">
    <div class="value-visual" aria-hidden="true">
      <div class="value-center"><svg viewBox="0 0 32 32" fill="none"><path d="M16 2.8 28.5 10v12L16 29.2 3.5 22V10L16 2.8Z" stroke="currentColor" stroke-width="1.8"/><path d="m16 8.5 7.5 4.3v6.4L16 23.5l-7.5-4.3v-6.4L16 8.5Z" stroke="currentColor" stroke-width="1.4"/></svg><b>فناوریِ هم‌مسیر</b></div>
      <span class="value-node node-a"><svg viewBox="0 0 24 24" fill="none"><path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.5"/></svg> طراحی</span>
      <span class="value-node node-b"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="3" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/><rect x="4" y="14" width="16" height="7" rx="2" stroke="currentColor" stroke-width="1.5"/></svg> زیرساخت</span>
      <span class="value-node node-c"><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 20 6v5c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-3Z" stroke="currentColor" stroke-width="1.5"/></svg> امنیت</span>
      <span class="value-node node-d"><svg viewBox="0 0 24 24" fill="none"><path d="M4 13v-1a8 8 0 0 1 16 0v1M3 12h4v7H5a2 2 0 0 1-2-2v-5Zm14 0h4v5a2 2 0 0 1-2 2h-2v-7Z" stroke="currentColor" stroke-width="1.5"/></svg> پشتیبانی</span>
    </div>
    <div class="value-copy"><div class="section-kicker">رویکرد ایده‌بان</div><h2>فناوری باید مسئله‌ای واقعی را حل کند</h2><p>از انتخاب راهکار تا تحویل و پشتیبانی، مسیر همکاری با نیاز کسب‌وکار شروع می‌شود. به‌جای تعهدهای مبهم، محدوده و هزینه را در پیشنهاد اجرایی شفاف می‌کنیم.</p>
      <div class="value-list">
        <div class="value-item"><span class="check-icon"><svg viewBox="0 0 20 20" fill="none"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><strong>نیازسنجی پیش از پیشنهاد</strong><p>راهکار بر اساس هدف، وضعیت فعلی و محدودیت‌های کسب‌وکار بررسی می‌شود.</p></div></div>
        <div class="value-item"><span class="check-icon"><svg viewBox="0 0 20 20" fill="none"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><strong>محدوده و هزینه شفاف</strong><p>اقلام شامل، موارد خارج از محدوده و هزینه‌های جانبی در پیش‌فاکتور مشخص می‌شوند.</p></div></div>
        <div class="value-item"><span class="check-icon"><svg viewBox="0 0 20 20" fill="none"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><strong>نگاه یکپارچه به چرخه فناوری</strong><p>طراحی، استقرار، امنیت و نگهداری در کنار هم دیده می‌شوند.</p></div></div>
      </div>
    </div>
  </div>
</section>

<section class="section section-dark" id="process">
  <div class="container">
    <div class="section-heading"><div><div class="section-kicker">مسیر همکاری</div><h2>یک مسیر روشن از درخواست تا تحویل</h2></div><p>هر پروژه با توافق بر نیاز، زمان‌بندی و شرایط اجرا شروع می‌شود. خدمات حضوری یا وابسته به زیرساخت نیز پیش از تعهد بررسی خواهند شد.</p></div>
    <div class="steps-grid">
      <article class="step-card"><h3>ثبت درخواست</h3><p>نیاز اولیه، مسئله و راه ارتباطی را از طریق فرم مشاوره ثبت می‌کنید.</p></article>
      <article class="step-card"><h3>نیازسنجی</h3><p>ابعاد کار، وضعیت فعلی، وابستگی‌ها و محدودیت‌های اجرا بررسی می‌شوند.</p></article>
      <article class="step-card"><h3>پیشنهاد و توافق</h3><p>محدوده، اقلام تحویلی، زمان‌بندی و هزینه در پیشنهاد یا پیش‌فاکتور می‌آید.</p></article>
      <article class="step-card"><h3>اجرا و پیگیری</h3><p>پس از تأیید، کار طبق توافق پیش می‌رود و مسیر پشتیبانی مشخص می‌شود.</p></article>
    </div>
  </div>
</section>

<section class="section section-soft" id="plans">
  <div class="container">
    <div class="section-heading"><div><div class="section-kicker">پلن‌های همکاری</div><h2>نقطه شروع را انتخاب کنید</h2></div><p>پلن‌ها چارچوب اولیه برای گفت‌وگو هستند؛ مبلغ نهایی بر اساس محدوده کار و نیاز واقعی، پس از بررسی اعلام می‌شود.</p></div>
    <div class="plans-grid">
      <?php foreach ($plans as $plan): ?>
        <article class="plan-card <?= !empty($plan['is_featured']) ? 'featured' : '' ?>">
          <?php if (!empty($plan['is_featured'])): ?><span class="plan-badge">پیشنهاد برای شروع گفتگو</span><?php endif; ?>
          <div class="plan-type"><?= e($plan['audience']) ?></div><h3><?= e($plan['title']) ?></h3><p><?= e($plan['summary']) ?></p>
          <?php $currentPriceInfo = $plan['price_info'] ?? []; require APP_ROOT . '/app/Views/partials/price-info.php'; ?>
          <ul class="plan-list"><?php foreach (array_slice($plan['features'] ?? [], 0, 5) as $feature): ?><li><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><?= e($feature['feature_text']) ?></li><?php endforeach; ?></ul>
          <a class="btn <?= !empty($plan['is_featured']) ? 'btn-dark' : 'btn-outline' ?>" href="<?= e(site_url('/contact#contact')) ?>">درخواست برآورد</a>
        </article>
      <?php endforeach; ?>
      <?php if ($plans === []): ?><div class="empty-state">پلن‌ها پس از تکمیل اطلاعات در پنل مدیریت نمایش داده می‌شوند.</div><?php endif; ?>
    </div>
    <div class="plan-footnote">هیچ تعرفه‌ای بدون منبع معتبر به‌عنوان نرخ رسمی منتشر نمی‌شود. قیمت‌های نامشخص «استعلام قیمت» هستند.</div>
  </div>
</section>

<section class="section" id="portfolio">
  <div class="container">
    <div class="section-heading"><div><div class="section-kicker">نمونه‌کارها</div><h2>اعتماد با جزئیات واقعی ساخته می‌شود</h2></div><p>مطالعات موردی فقط پس از تأیید اطلاعات و مجوز انتشار مشتری در این بخش قرار می‌گیرند.</p></div>
    <div class="portfolio-empty"><div class="empty-art" aria-hidden="true"><svg viewBox="0 0 36 36" fill="none"><rect x="4" y="5" width="28" height="25" rx="4" stroke="currentColor" stroke-width="1.7"/><path d="M4 12h28M10 8.5h.01M14 8.5h.01M11 20h6m-6 4h14m-4-8 3 3 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div><h3>پروژه‌های قابل انتشار در حال آماده‌سازی‌اند</h3><p>برای معرفی یک پروژه واقعی، مسئله، راهکار، فناوری‌ها و نتیجه قابل انتشار به‌همراه مجوز مشتری ثبت می‌شود. تا آن زمان، نمونه‌کار ساختگی نمایش نمی‌دهیم.</p></div><a class="btn btn-outline" href="<?= e(site_url('/portfolio')) ?>">صفحه نمونه‌کارها</a></div>
  </div>
</section>

<section class="section section-soft" id="about-founder">
  <div class="container"><div class="founder-panel"><div class="founder-copy"><div class="section-kicker">درباره بنیان‌گذار</div><h2><?= e($founder) ?></h2><div class="role"><?= e($founderTitle) ?></div><p><?= e($company) ?> به ارائه راهکارهای فناوری اطلاعات برای کسب‌وکارها می‌پردازد؛ از توسعه وب و نرم‌افزار تا استقرار، امنیت، شبکه و پشتیبانی. شیوه و محدوده هر همکاری با توجه به نیاز و شرایط اجرا مشخص می‌شود.</p></div><div class="founder-mark" aria-hidden="true"><svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="13" r="7" stroke="currentColor" stroke-width="1.7"/><path d="M6 35c1.8-7 6.5-10.5 14-10.5S32.2 28 34 35" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div></div></div>
</section>

<section class="section" id="faq">
  <div class="container faq-grid">
    <div class="faq-intro"><div class="section-kicker">پرسش‌های پرتکرار</div><h2>پیش از شروع همکاری</h2><p>اگر پاسخ سؤال خود را پیدا نکردید، نیازتان را ثبت کنید تا برای بررسی با شما تماس بگیریم.</p><a class="text-link" href="<?= e(site_url('/contact#contact')) ?>">گفت‌وگو با ما <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div>
    <div class="faq-list">
      <div class="faq-item"><button class="faq-question" type="button" aria-expanded="false"><span>هزینه خدمات چگونه مشخص می‌شود؟</span><span aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></button><div class="faq-answer">پس از نیازسنجی، محدوده کار، اقلام شامل و هزینه‌های جانبی مشخص می‌شوند و برآورد یا پیش‌فاکتور ارائه می‌شود. موارد فاقد قیمت تأییدشده با عنوان «استعلام قیمت» نمایش داده می‌شوند.</div></div>
      <div class="faq-item"><button class="faq-question" type="button" aria-expanded="false"><span>آیا خدمات حضوری هم ارائه می‌شود؟</span><span aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></button><div class="faq-answer">خدمات حضوری مانند شبکه و تجهیزات، با توجه به محدوده جغرافیایی، امکان اجرا و توافق قراردادی بررسی و اعلام می‌شوند.</div></div>
      <div class="faq-item"><button class="faq-question" type="button" aria-expanded="false"><span>برای دریافت پیش‌فاکتور چه اطلاعاتی لازم است؟</span><span aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></button><div class="faq-answer">درخواست اولیه و راه تماس کافی است. اگر شرح کوتاهی از هدف، وضعیت موجود، نیازها و محدودیت زمانی دارید، واردکردن آن به دقیق‌ترشدن نیازسنجی کمک می‌کند.</div></div>
      <div class="faq-item"><button class="faq-question" type="button" aria-expanded="false"><span>نمونه‌کارها و نظرات مشتریان چه زمانی منتشر می‌شوند؟</span><span aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></button><div class="faq-answer">پس از ثبت اطلاعات واقعی پروژه و تأیید مجوز انتشار. تا آن زمان، پروژه یا نظر فرضی به‌عنوان تجربه واقعی نمایش داده نمی‌شود.</div></div>
    </div>
  </div>
</section>

<section class="contact-section" id="contact">
  <div class="container contact-grid">
    <div class="contact-copy"><div class="section-kicker">شروع گفت‌وگو</div><h2>مسئله فناوری کسب‌وکارتان را با ما در میان بگذارید</h2><p>درخواست را ثبت کنید تا برای بررسی اولیه و تعیین مسیر مناسب با شما تماس بگیریم. اطلاعات شما فقط برای پیگیری همین درخواست استفاده می‌شود.</p>
      <div class="contact-details"><div class="contact-detail"><span class="detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M6.5 3.5h3L11 8l-2 1.5a15 15 0 0 0 5.5 5.5L16 13l4.5 1.5v3c0 1.1-.9 2-2 2A15 15 0 0 1 4.5 5.5c0-1.1.9-2 2-2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span><span><small>تماس مستقیم</small><a href="tel:<?= e($tel) ?>" dir="ltr"><?= e($phone) ?></a></span></div><div class="contact-detail"><span class="detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="9" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg></span><span><small>خدمات حضوری</small>بر اساس محدوده و توافق</span></div></div>
    </div>
    <form class="contact-form" action="<?= e(site_url('/contact')) ?>" method="post">
      <?= csrf_field() ?><input type="hidden" name="lead_type" value="consultation"><input type="hidden" name="source" value="<?= e($leadSource ?? 'website') ?>">
      <div class="honeypot" aria-hidden="true"><label>وب‌سایت<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="form-grid">
        <div class="field"><label for="full_name">نام و نام خانوادگی *</label><input id="full_name" name="full_name" type="text" required maxlength="160" autocomplete="name" placeholder="نام شما"></div>
        <div class="field"><label for="phone">شماره تماس *</label><input id="phone" name="phone" type="tel" required maxlength="32" autocomplete="tel" placeholder="مثلاً 0912…" dir="ltr"></div>
        <div class="field"><label for="company_name">نام شرکت</label><input id="company_name" name="company_name" type="text" maxlength="180" autocomplete="organization" placeholder="در صورت تمایل"></div>
        <div class="field"><label for="email">ایمیل</label><input id="email" name="email" type="email" maxlength="190" autocomplete="email" placeholder="name@example.com" dir="ltr"></div>
        <div class="field full"><label for="requested_service">خدمت موردنیاز</label><select id="requested_service" name="requested_service"><option value="">انتخاب خدمت (اختیاری)</option><?php foreach ($services as $service): ?><option value="<?= e($service['title']) ?>"><?= e($service['title']) ?></option><?php endforeach; ?><option value="مشاوره و نیازسنجی">مشاوره و نیازسنجی</option></select></div>
        <div class="field full"><label for="description">توضیحات کوتاه</label><textarea id="description" name="description" maxlength="8000" placeholder="چه مسئله‌ای را می‌خواهید حل کنید؟"></textarea></div>
      </div>
      <div class="form-footer"><label class="privacy-note"><input type="checkbox" name="privacy_consent" value="1" required> با استفاده از اطلاعات تماس برای پیگیری درخواست موافقم.</label><button class="btn btn-primary" type="submit">ثبت درخواست <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-5-5m5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
      <div class="form-message" role="status"></div>
    </form>
  </div>
</section>
