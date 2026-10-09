<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="<?= e(site_url('/')) ?>">خانه</a> / نمونه‌کارها</div><div class="eyebrow"><span class="dot"></span> مطالعات موردی</div><h1>پروژه‌ها با اطلاعات واقعی معرفی می‌شوند</h1><p>شرح مسئله، راهکار، فناوری‌ها و نتیجه هر پروژه تنها پس از تأیید و دریافت مجوز انتشار در اینجا قرار می‌گیرد.</p></div></section>
<section class="section section-soft"><div class="container">
<?php if ($projects === []): ?>
  <div class="portfolio-empty"><div class="empty-art" aria-hidden="true"><svg viewBox="0 0 36 36" fill="none"><rect x="4" y="5" width="28" height="25" rx="4" stroke="currentColor" stroke-width="1.7"/><path d="M4 12h28M10 8.5h.01M14 8.5h.01M11 20h6m-6 4h14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><div><h3>هنوز پروژه‌ای برای انتشار عمومی ثبت نشده است</h3><p>ترجیح می‌دهیم به‌جای نمایش محتوای فرضی، هر پروژه را با جزئیات قابل‌بررسی و مجوز انتشار معرفی کنیم.</p></div><a class="btn btn-dark" href="<?= e(site_url('/contact#contact')) ?>">گفت‌وگو درباره پروژه</a></div>
<?php else: ?>
  <div class="listing-grid"><?php foreach ($projects as $project): ?><article class="content-card"><span class="section-kicker"><?= e($project['category'] ?: 'پروژه') ?></span><h2><?= e($project['title']) ?></h2><p><?= e($project['problem'] ?: 'شرح پروژه در حال تکمیل است.') ?></p><a class="btn btn-outline" href="<?= e(site_url('/portfolio/' . $project['slug'])) ?>">مطالعه موردی</a></article><?php endforeach; ?></div>
<?php endif; ?>
</div></section>
