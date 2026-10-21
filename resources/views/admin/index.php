<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-6 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow text-lime-400">Restricted Administration</span>
            <h1 class="text-3xl font-extrabold tracking-tight">System Oversight Portal</h1>
            <p class="text-gray-400 text-sm mt-1">Platform moderation, verification dispute oversight, and network security audit</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2 font-mono text-xs">
            <a href="/admin/reports" class="btn btn-outline btn-sm">Reports Moderation</a>
            <a href="/admin/claims" class="btn btn-outline btn-sm">Claims Pipeline</a>
            <a href="/admin/users" class="btn btn-outline btn-sm">Users Directory</a>
            <a href="/admin/audit" class="btn btn-outline btn-sm">Audit Logs</a>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 font-mono">
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Total Database Reports</div>
            <div class="text-3xl font-extrabold text-[#F4F2EA] mt-1"><?= $stats['total'] ?></div>
        </div>
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Registered Users</div>
            <div class="text-3xl font-extrabold text-lime-400 mt-1"><?= $totalUsers ?></div>
        </div>
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Total Recovered</div>
            <div class="text-3xl font-extrabold text-green-400 mt-1"><?= $stats['recovered'] ?></div>
        </div>
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Success Rate</div>
            <div class="text-3xl font-extrabold text-[#F4F2EA] mt-1"><?= $stats['recovery_rate'] ?>%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Recent Claims for Review -->
        <div class="lg:col-span-6 panel">
            <div class="panel-header">
                <span class="panel-title">Claims Oversight Queue</span>
                <a href="/admin/claims" class="text-xs font-mono text-lime-400 hover:underline">View All &rarr;</a>
            </div>

            <?php if (empty($pendingClaims)): ?>
                <div class="text-center py-8 text-gray-500 text-xs">No claims currently pending review.</div>
            <?php else: ?>
                <div class="space-y-3 text-xs">
                    <?php foreach ($pendingClaims as $c): ?>
                        <div class="p-3 bg-[#111310] border border-[#2A3026] flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-gray-400"><?= Helper::escape($c['report_identifier']) ?></span>
                                    <?= Helper::statusBadge($c['status']) ?>
                                </div>
                                <div class="font-bold text-gray-200"><?= Helper::escape($c['report_title']) ?></div>
                                <div class="text-gray-400 text-[11px] font-mono">Claimant: <?= Helper::escape($c['claimant_name']) ?></div>
                            </div>
                            <a href="/claims/<?= $c['id'] ?>" class="btn btn-outline btn-sm">Inspect &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Audit Activity -->
        <div class="lg:col-span-6 panel">
            <div class="panel-header">
                <span class="panel-title">System Audit Log Trail</span>
                <a href="/admin/audit" class="text-xs font-mono text-lime-400 hover:underline">View All &rarr;</a>
            </div>

            <div class="space-y-2 text-xs font-mono">
                <?php foreach ($recentAudit as $log): ?>
                    <div class="p-2.5 bg-[#111310] border border-[#2A3026] flex items-center justify-between">
                        <div>
                            <span class="text-lime-400 font-bold"><?= Helper::escape($log['action']) ?></span>
                            <span class="text-gray-400 text-[11px] block"><?= Helper::escape($log['details']) ?></span>
                        </div>
                        <span class="text-[10px] text-gray-500"><?= Helper::formatDate($log['created_at'], 'H:i:s') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
