<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Moderation Authority</span>
            <h1 class="text-3xl font-extrabold tracking-tight">Reports Moderation (<?= $total ?>)</h1>
            <p class="text-gray-400 text-sm mt-1">Review flagged signals, enforce safety guidelines, and manage report states</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/admin" class="btn btn-outline btn-sm font-mono">&larr; Back to Admin Overview</a>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="/admin/reports" method="GET" class="panel p-4 mb-6 flex flex-wrap gap-3 items-center text-xs font-mono">
        <input type="text" name="q" value="<?= Helper::escape($filters['q']) ?>" placeholder="Search title or ID..." class="form-control text-xs w-48">
        
        <select name="type" class="form-control text-xs w-36">
            <option value="">All Types</option>
            <option value="lost" <?= ($filters['type'] === 'lost') ? 'selected' : '' ?>>Lost Only</option>
            <option value="found" <?= ($filters['type'] === 'found') ? 'selected' : '' ?>>Found Only</option>
        </select>

        <select name="status" class="form-control text-xs w-36">
            <option value="">All Statuses</option>
            <option value="active" <?= ($filters['status'] === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="under_review" <?= ($filters['status'] === 'under_review') ? 'selected' : '' ?>>Under Review</option>
            <option value="claimed" <?= ($filters['status'] === 'claimed') ? 'selected' : '' ?>>Claimed</option>
            <option value="recovered" <?= ($filters['status'] === 'recovered') ? 'selected' : '' ?>>Recovered</option>
            <option value="expired" <?= ($filters['status'] === 'expired') ? 'selected' : '' ?>>Expired</option>
            <option value="closed" <?= ($filters['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
        </select>

        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="/admin/reports" class="text-gray-400 hover:text-lime-400 ml-auto">Reset</a>
    </form>

    <!-- Table -->
    <div class="panel p-0 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#111310] font-mono border-b border-[#2A3026] text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="p-3">Identifier</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Title &amp; Category</th>
                    <th class="p-3">Location</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Date</th>
                    <th class="p-3 text-right">Moderation Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3026]">
                <?php foreach ($reports as $r): ?>
                    <tr class="hover:bg-[#111310]/50">
                        <td class="p-3 font-mono font-bold text-lime-400"><?= Helper::escape($r['identifier']) ?></td>
                        <td class="p-3"><?= Helper::typeBadge($r['report_type']) ?></td>
                        <td class="p-3">
                            <a href="/reports/<?= $r['id'] ?>" class="font-bold text-gray-200 hover:text-lime-400 block"><?= Helper::escape($r['item_title']) ?></a>
                            <span class="text-gray-500 font-mono text-[10px]"><?= Helper::escape($r['category']) ?></span>
                        </td>
                        <td class="p-3 font-mono text-gray-300"><?= Helper::escape($r['general_location']) ?></td>
                        <td class="p-3"><?= Helper::statusBadge($r['status']) ?></td>
                        <td class="p-3 font-mono text-gray-400"><?= Helper::formatDate($r['event_date']) ?></td>
                        <td class="p-3 text-right">
                            <form action="/admin/reports/<?= $r['id'] ?>/status" method="POST" class="inline-flex items-center gap-1">
                                <?= Helper::csrfField() ?>
                                <select name="status" class="form-control text-[11px] py-1 px-2 font-mono">
                                    <option value="active" <?= ($r['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="under_review" <?= ($r['status'] === 'under_review') ? 'selected' : '' ?>>Under Review</option>
                                    <option value="claimed" <?= ($r['status'] === 'claimed') ? 'selected' : '' ?>>Claimed</option>
                                    <option value="recovered" <?= ($r['status'] === 'recovered') ? 'selected' : '' ?>>Recovered</option>
                                    <option value="expired" <?= ($r['status'] === 'expired') ? 'selected' : '' ?>>Expired</option>
                                    <option value="closed" <?= ($r['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
                                </select>
                                <button type="submit" class="btn btn-outline btn-sm py-1 px-2">Set</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 font-mono text-xs mt-6">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="/admin/reports?page=<?= $p ?>" class="px-3 py-1.5 border <?= ($p === $currentPage) ? 'border-lime-400 bg-lime-400 text-black font-bold' : 'border-[#2A3026] text-gray-300' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
