<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="mb-8 pb-4 border-b border-[#2A3026]">
        <span class="editorial-eyebrow">Ownership Resolution Pipeline</span>
        <h1 class="text-3xl font-extrabold tracking-tight">Claims Management</h1>
        <p class="text-gray-400 text-sm mt-1">Review incoming claims on items you found or track the progress of your own claims</p>
    </div>

    <div class="space-y-8">
        <!-- Section 1: Claims on Items Found by Me -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-lime-400"></span>
                    Claims Received on Items You Found (<?= count($claimsOnMyItems) ?>)
                </span>
            </div>

            <?php if (empty($claimsOnMyItems)): ?>
                <div class="text-center py-8 text-gray-500 text-xs">
                    No claims currently filed on items you reported finding.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($claimsOnMyItems as $c): ?>
                        <div class="p-3 bg-[#111310] border border-[#2A3026] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= Helper::uploadUrl($c['report_image']) ?>" class="w-12 h-12 object-cover border border-[#2A3026]">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($c['report_identifier']) ?></span>
                                        <?= Helper::statusBadge($c['status']) ?>
                                    </div>
                                    <div class="font-bold text-sm text-gray-100"><?= Helper::escape($c['report_title']) ?></div>
                                    <div class="text-xs text-gray-400 font-mono">Claimant: <?= Helper::escape($c['claimant_name']) ?> &bull; <?= Helper::timeAgo($c['created_at']) ?></div>
                                </div>
                            </div>
                            <a href="/claims/<?= $c['id'] ?>" class="btn btn-primary btn-sm self-end sm:self-center">Review Evidence &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 2: Claims Submitted by Me -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Claims Submitted by You (<?= count($myClaims) ?>)
                </span>
            </div>

            <?php if (empty($myClaims)): ?>
                <div class="text-center py-8 text-gray-500 text-xs">
                    You have not submitted any ownership claims yet.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($myClaims as $c): ?>
                        <div class="p-3 bg-[#111310] border border-[#2A3026] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= Helper::uploadUrl($c['report_image']) ?>" class="w-12 h-12 object-cover border border-[#2A3026]">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs text-amber-400 font-bold"><?= Helper::escape($c['report_identifier']) ?></span>
                                        <?= Helper::statusBadge($c['status']) ?>
                                    </div>
                                    <div class="font-bold text-sm text-gray-100"><?= Helper::escape($c['report_title']) ?></div>
                                    <div class="text-xs text-gray-400 font-mono">Submitted: <?= Helper::formatDate($c['created_at']) ?></div>
                                </div>
                            </div>
                            <a href="/claims/<?= $c['id'] ?>" class="btn btn-outline btn-sm self-end sm:self-center">View Status &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
