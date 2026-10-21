<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Claims Oversight</span>
            <h1 class="text-3xl font-extrabold tracking-tight">All Ownership Claims</h1>
            <p class="text-gray-400 text-sm mt-1">Audit claim verification decisions, disputed items, and return confirmations</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/admin" class="btn btn-outline btn-sm font-mono">&larr; Back to Admin Overview</a>
        </div>
    </div>

    <div class="panel p-0 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#111310] font-mono border-b border-[#2A3026] text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="p-3">Claim ID</th>
                    <th class="p-3">Report Item</th>
                    <th class="p-3">Claimant</th>
                    <th class="p-3">Finder</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Created</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3026]">
                <?php foreach ($claims as $c): ?>
                    <tr class="hover:bg-[#111310]/50">
                        <td class="p-3 font-mono font-bold text-gray-300">#<?= $c['id'] ?></td>
                        <td class="p-3">
                            <span class="font-bold text-gray-200"><?= Helper::escape($c['report_title']) ?></span>
                            <span class="font-mono text-gray-500 text-[10px] block"><?= Helper::escape($c['report_identifier']) ?></span>
                        </td>
                        <td class="p-3 font-mono text-gray-300"><?= Helper::escape($c['claimant_name']) ?></td>
                        <td class="p-3 font-mono text-gray-300"><?= Helper::escape($c['finder_name']) ?></td>
                        <td class="p-3"><?= Helper::statusBadge($c['status']) ?></td>
                        <td class="p-3 font-mono text-gray-400"><?= Helper::formatDate($c['created_at']) ?></td>
                        <td class="p-3 text-right">
                            <a href="/claims/<?= $c['id'] ?>" class="btn btn-outline btn-sm">Inspect Case &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
