<?php
use App\Helpers\Helper;
?>
<a href="/reports/<?= $report['id'] ?>" class="item-card">
    <div class="item-card-image-wrap">
        <img src="<?= Helper::uploadUrl($report['primary_image'] ?? null) ?>" 
             alt="<?= Helper::escape($report['item_title']) ?>" 
             class="item-card-img" 
             loading="lazy">
        <div class="absolute top-2 left-2 flex items-center gap-1.5">
            <?= Helper::typeBadge($report['report_type']) ?>
        </div>
        <div class="absolute top-2 right-2">
            <?= Helper::statusBadge($report['status']) ?>
        </div>
    </div>
    <div class="item-card-body">
        <div class="item-card-meta">
            <span class="font-mono text-xs text-gray-400"><?= Helper::escape($report['category']) ?></span>
            <span class="font-mono text-xs text-lime-400"><?= Helper::escape($report['identifier']) ?></span>
        </div>
        <h3 class="item-card-title"><?= Helper::escape($report['item_title']) ?></h3>
        <p class="item-card-desc"><?= Helper::escape($report['description']) ?></p>
        <div class="item-card-footer">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <?= Helper::escape($report['general_location']) ?>
            </span>
            <span><?= Helper::formatDate($report['event_date']) ?></span>
        </div>
    </div>
</a>
