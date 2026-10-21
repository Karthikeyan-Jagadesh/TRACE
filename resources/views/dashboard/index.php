<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <!-- Top Welcome & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-6 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">User Operational Terminal</span>
            <h1 class="text-3xl font-extrabold tracking-tight">Intelligence Dashboard</h1>
            <p class="text-gray-400 text-sm mt-1">
                Connected as <strong class="text-gray-200"><?= Helper::escape($authUser['name']) ?></strong> (<?= Helper::escape($authUser['email']) ?>)
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="/reports/create/lost" class="btn btn-primary btn-sm">+ Report Lost</a>
            <a href="/reports/create/found" class="btn btn-outline btn-sm">+ Report Found</a>
            <a href="/settings" class="btn btn-outline btn-sm font-mono">Settings</a>
        </div>
    </div>

    <!-- 4 Key Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 font-mono">
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">My Active Reports</div>
            <div class="text-3xl font-extrabold text-lime-400 mt-1"><?= $totalReports ?></div>
            <a href="/dashboard/reports" class="text-[11px] text-gray-400 hover:text-lime-400 mt-2 block">Manage reports &rarr;</a>
        </div>
        <div class="panel border-lime-400/40">
            <div class="text-xs text-gray-500 uppercase">Potential Matches</div>
            <div class="text-3xl font-extrabold text-lime-400 mt-1"><?= $totalMatches ?></div>
            <a href="/matches" class="text-[11px] text-lime-400 hover:underline mt-2 block">Review correlations &rarr;</a>
        </div>
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Pending Claims</div>
            <div class="text-3xl font-extrabold text-amber-400 mt-1"><?= $pendingClaimsCount ?></div>
            <a href="/claims" class="text-[11px] text-gray-400 hover:text-amber-400 mt-2 block">Track claim status &rarr;</a>
        </div>
        <div class="panel">
            <div class="text-xs text-gray-500 uppercase">Items Recovered</div>
            <div class="text-3xl font-extrabold text-[#F4F2EA] mt-1"><?= $recoveredCount ?></div>
            <span class="text-[11px] text-gray-500 mt-2 block">Successful outcomes</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: My Active Reports (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">My Recent Reports</span>
                    <a href="/dashboard/reports" class="text-xs font-mono text-lime-400 hover:underline">View All (<?= $totalReports ?>) &rarr;</a>
                </div>

                <?php if (empty($myReports)): ?>
                    <div class="text-center py-8 text-gray-500 text-xs">
                        You have not filed any lost or found reports yet.<br>
                        <a href="/reports/create/lost" class="text-lime-400 font-semibold hover:underline mt-2 inline-block">+ Create your first report</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($myReports as $r): ?>
                            <div class="p-3 bg-[#111310] border border-[#2A3026] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="<?= Helper::uploadUrl($r['primary_image']) ?>" class="w-12 h-12 object-cover border border-[#2A3026]">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <?= Helper::typeBadge($r['report_type']) ?>
                                            <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($r['identifier']) ?></span>
                                            <?= Helper::statusBadge($r['status']) ?>
                                        </div>
                                        <a href="/reports/<?= $r['id'] ?>" class="font-bold text-sm text-gray-200 hover:text-lime-400">
                                            <?= Helper::escape($r['item_title']) ?>
                                        </a>
                                        <div class="text-xs text-gray-400 mt-0.5 font-mono">
                                            <?= Helper::escape($r['general_location']) ?> &bull; <?= Helper::formatDate($r['event_date']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <?php if ($r['match_count'] > 0): ?>
                                        <a href="/reports/<?= $r['id'] ?>" class="text-[11px] font-mono text-lime-400 font-bold block mb-1">
                                            <?= $r['match_count'] ?> Matches
                                        </a>
                                    <?php endif; ?>
                                    <a href="/reports/<?= $r['id'] ?>" class="btn btn-outline btn-sm">Inspect</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Active Claims -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Claims Pipeline</span>
                    <a href="/claims" class="text-xs font-mono text-lime-400 hover:underline">Manage All &rarr;</a>
                </div>

                <?php 
                $activeClaims = array_merge($myClaims, $claimsOnMyItems);
                if (empty($activeClaims)): ?>
                    <div class="text-center py-6 text-gray-500 text-xs">
                        No active ownership claims currently pending review.
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach (array_slice($activeClaims, 0, 4) as $claim): ?>
                            <div class="p-3 bg-[#111310] border border-[#2A3026] flex items-center justify-between text-xs">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono font-bold text-gray-400">CLAIM #<?= $claim['id'] ?></span>
                                        <?= Helper::statusBadge($claim['status']) ?>
                                    </div>
                                    <div class="font-bold text-gray-200"><?= Helper::escape($claim['report_title']) ?></div>
                                </div>
                                <a href="/claims/<?= $claim['id'] ?>" class="btn btn-outline btn-sm">Review &rarr;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Potential Match Suggestions (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="panel border-lime-400/40">
                <div class="panel-header">
                    <span class="panel-title flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                        Top Potential Matches
                    </span>
                    <a href="/matches" class="text-xs font-mono text-lime-400 hover:underline">All &rarr;</a>
                </div>

                <?php if (empty($pendingMatches)): ?>
                    <div class="text-center py-8 text-gray-500 text-xs">
                        No unresolved high-confidence matches detected yet. We will notify you when a correlated report is logged.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($pendingMatches as $match): ?>
                            <div class="p-3 bg-[#111310] border border-[#2A3026] text-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-gray-400">Pair Correlation</span>
                                    <?= Helper::scoreBadge((int)$match['score']) ?>
                                </div>
                                <div class="font-bold text-gray-200 mb-1">
                                    <?= Helper::escape($match['lost_title']) ?> &harr; <?= Helper::escape($match['found_title']) ?>
                                </div>
                                <p class="text-[11px] text-gray-400 mb-3 border-l-2 border-lime-400 pl-2 italic">
                                    "<?= Helper::escape($match['explanation']) ?>"
                                </p>
                                <div class="flex justify-end gap-2">
                                    <a href="/reports/<?= $match['lost_report_id'] ?>" class="btn btn-outline btn-sm">Lost Report</a>
                                    <a href="/reports/<?= $match['found_report_id'] ?>" class="btn btn-primary btn-sm">Found Report &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Guidelines -->
            <div class="panel text-xs text-gray-400 space-y-2">
                <div class="font-mono text-gray-200 uppercase font-bold tracking-wider">Operational Protocol</div>
                <p>When an item is returned, verify that both parties confirm handover so the report status updates to <strong>Recovered</strong>.</p>
            </div>
        </div>
    </div>
</div>
