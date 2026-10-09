<?php
$currentPriceInfo = $currentPriceInfo ?? [];
$components = $currentPriceInfo['components'] ?? [];
$priceContainerClass = $priceContainerClass ?? 'plan-price';
?>
<div class="<?= e($priceContainerClass) ?>">
<?php if ($components !== []): ?>
  <?php foreach ($components as $component): ?>
    <div class="price-component"><b><?= e($component['component_label']) ?></b><strong><?= e($component['label']) ?></strong><small><?= e($component['type_label']) ?><?= !empty($component['unit']) ? ' · واحد: ' . e($component['unit']) : '' ?></small><?php if (!empty($component['source_url'])): ?><small><a href="<?= e($component['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($component['source_title'] ?: 'مشاهده منبع') ?></a></small><?php endif; ?></div>
  <?php endforeach; ?>
<?php else: ?>
  <strong><?= e($currentPriceInfo['label'] ?? 'استعلام قیمت') ?></strong><small><?= e($currentPriceInfo['type_label'] ?? 'قیمت پس از نیازسنجی اعلام می‌شود') ?></small>
<?php endif; ?>
</div>
