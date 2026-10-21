<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-6 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Personal Signal Registry</span>
            <h1 class="text-3xl font-extrabold tracking-tight">My Reports (<?= $total ?>)</h1>
            <p class="text-gray-400 text-sm mt-1">Manage and inspect all incident signals initiated by your account</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="/reports/create/lost" class="btn btn-primary btn-sm">+ Report Lost Item</a>
            <a href="/reports/create/found" class="btn btn-outline btn-sm">+ Report Found Item</a>
        </div>
    </div>

    <?php if (empty($reports)): ?>
        <div class="panel text-center py-16 text-gray-500 text-sm">
            You have not registered any lost or found reports.<br>
            <a href="/reports/create/lost" class="text-lime-400 font-bold hover:underline mt-2 inline-block">+ Create your first report</a>
        </div>
    <?php else: ?>
        <div class="space-y-4 mb-8">
            <?php foreach ($reports as $r): ?>
                <div class="panel flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="<?= Helper::uploadUrl($r['primary_image']) ?>" class="w-16 h-16 object-cover border border-[#2A3026] flex-shrink-0">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <?= Helper::typeBadge($r['report_type']) ?>
                                <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($r['identifier']) ?></span>
                                <?= Helper::statusBadge($r['status']) ?>
                            </div>
                            <h2 class="text-base font-bold text-gray-100"><?= Helper::escape($r['item_title']) ?></h2>
                            <div class="text-xs text-gray-400 font-mono mt-0.5">
                                <?= Helper::escape($r['category']) ?> &bull; <?= Helper::escape($r['general_location']) ?> &bull; <?= Helper::formatDate($r['event_date']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 self-end md:self-center font-mono text-xs">
                        <?php if ($r['match_count'] > 0): ?>
                            <span class="px-2.5 py-1 bg-lime-400/10 text-lime-400 border border-lime-400/30 font-bold">
                                <?= $r['match_count'] ?> Matches
                            </span>
                        <?php endif; ?>

                        <?php if ($r['claim_count'] > 0): ?>
                            <span class="px-2.5 py-1 bg-blue-400/10 text-blue-400 border border-blue-400/30 font-bold">
                                <?= $r['claim_count'] ?> Claims
                            </span>
                        <?php endif; ?>

                        <a href="/reports/<?= $r['id'] ?>" class="btn btn-outline btn-sm">Inspect Details &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center gap-2 font-mono text-xs">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="/dashboard/reports?page=<?= $p ?>" 
                       class="px-3 py-1.5 border <?= ($p === $currentPage) ? 'border-lime-400 bg-lime-400 text-black font-bold' : 'border-[#2A3026] text-gray-300' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
