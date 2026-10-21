<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-12 max-w-2xl">
    <div class="panel border-lime-400 text-center p-8">
        <div class="w-12 h-12 rounded-full bg-lime-400 text-black flex items-center justify-center mx-auto mb-4 text-xl font-bold">
            &check;
        </div>

        <span class="editorial-eyebrow">Incident Ingested Successfully</span>
        <h1 class="text-2xl lg:text-3xl font-extrabold mb-2"><?= Helper::escape($report['item_title']) ?></h1>
        <div class="font-mono text-sm text-lime-400 font-bold mb-6">
            SIGNAL IDENTIFIER: <?= Helper::escape($report['identifier']) ?>
        </div>

        <p class="text-gray-300 text-sm leading-relaxed mb-6">
            Your <?= Helper::escape($report['report_type']) ?> report has been published to the TRACE Intelligence Network. 
            Our matching engine has evaluated opposite active reports.
        </p>

        <?php if (!empty($matches)): ?>
            <div class="p-4 bg-[#111310] border border-lime-400/40 text-left mb-6">
                <div class="font-bold text-sm text-lime-400 font-mono mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-lime-400 animate-ping"></span>
                    <?= count($matches) ?> Potential Match Candidate(s) Detected Immediately!
                </div>
                <div class="space-y-2 text-xs">
                    <?php foreach ($matches as $m): ?>
                        <div class="p-2 border border-[#2A3026] flex justify-between items-center">
                            <div>
                                <span class="font-mono text-gray-400"><?= Helper::escape($m['found_identifier'] ?? $m['lost_identifier']) ?></span>
                                <div class="font-semibold text-gray-200"><?= Helper::escape($m['found_title'] ?? $m['lost_title']) ?></div>
                            </div>
                            <span class="font-mono text-lime-400 font-bold"><?= $m['score'] ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="/reports/<?= $report['id'] ?>" class="btn btn-primary">
                View Report Details &rarr;
            </a>
            <a href="/dashboard" class="btn btn-outline">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
