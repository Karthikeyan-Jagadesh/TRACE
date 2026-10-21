<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Immutable Event Ledger</span>
            <h1 class="text-3xl font-extrabold tracking-tight">System Audit Logs</h1>
            <p class="text-gray-400 text-sm mt-1">Chronological record of authentication, claims, moderation, and state transitions</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/admin" class="btn btn-outline btn-sm font-mono">&larr; Back to Admin Overview</a>
        </div>
    </div>

    <div class="panel p-0 overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#111310] border-b border-[#2A3026] text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="p-3">Timestamp</th>
                    <th class="p-3">Action</th>
                    <th class="p-3">Actor</th>
                    <th class="p-3">Entity</th>
                    <th class="p-3">Details</th>
                    <th class="p-3 text-right">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3026]">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-[#111310]/50">
                        <td class="p-3 text-gray-500 text-[11px]"><?= Helper::formatDate($log['created_at'], 'M d Y H:i:s') ?></td>
                        <td class="p-3 text-lime-400 font-bold"><?= Helper::escape($log['action']) ?></td>
                        <td class="p-3 text-gray-300"><?= Helper::escape($log['user_name'] ?? 'System Daemon') ?></td>
                        <td class="p-3 text-gray-400"><?= Helper::escape($log['entity_type']) ?> #<?= $log['entity_id'] ?></td>
                        <td class="p-3 text-gray-200 font-sans text-xs"><?= Helper::escape($log['details']) ?></td>
                        <td class="p-3 text-right text-gray-500 text-[11px]"><?= Helper::escape($log['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
