<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-[#2A3026]">
        <div class="flex items-center gap-3">
            <a href="/search" class="text-xs font-mono text-gray-400 hover:text-lime-400">&larr; Back to Discovery</a>
            <span class="text-gray-600">/</span>
            <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($report['identifier']) ?></span>
        </div>
        <div class="flex items-center gap-2">
            <?= Helper::typeBadge($report['report_type']) ?>
            <?= Helper::statusBadge($report['status']) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Details (Left 7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Item Photo -->
            <div class="panel p-0 overflow-hidden">
                <img src="<?= Helper::uploadUrl($report['primary_image'] ?? null) ?>" 
                     alt="<?= Helper::escape($report['item_title']) ?>" 
                     class="w-full max-h-[420px] object-cover bg-[#1B1E19]">
                <?php if (count($images) > 1): ?>
                    <div class="flex gap-2 p-3 bg-[#111310] border-t border-[#2A3026] overflow-x-auto">
                        <?php foreach ($images as $img): ?>
                            <img src="<?= Helper::uploadUrl($img['file_path']) ?>" class="w-16 h-16 object-cover border border-[#2A3026]">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Item Overview -->
            <div class="panel">
                <div class="flex items-center justify-between text-xs font-mono text-gray-400 mb-2">
                    <span>Category: <strong class="text-gray-200"><?= Helper::escape($report['category']) ?></strong></span>
                    <span>Event Date: <strong class="text-gray-200"><?= Helper::formatDate($report['event_date']) ?></strong></span>
                </div>

                <h1 class="text-2xl lg:text-3xl font-extrabold mb-4"><?= Helper::escape($report['item_title']) ?></h1>

                <div class="text-sm leading-relaxed text-gray-300 space-y-3 mb-6 whitespace-pre-line">
                    <?= Helper::escape($report['description']) ?>
                </div>

                <!-- Technical Specification Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 bg-[#111310] border border-[#2A3026] font-mono text-xs">
                    <div>
                        <div class="text-gray-500 uppercase text-[10px]">Brand</div>
                        <div class="font-bold text-gray-200"><?= Helper::escape($report['brand'] ?: 'Not Specified') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 uppercase text-[10px]">Color</div>
                        <div class="font-bold text-gray-200"><?= Helper::escape($report['color'] ?: 'Not Specified') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 uppercase text-[10px]">Location</div>
                        <div class="font-bold text-gray-200"><?= Helper::escape($report['general_location']) ?></div>
                    </div>
                    <?php if (!empty($report['reward_amount'])): ?>
                        <div>
                            <div class="text-gray-500 uppercase text-[10px]">Reward</div>
                            <div class="font-bold text-lime-400">$<?= number_format($report['reward_amount'], 2) ?></div>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="text-gray-500 uppercase text-[10px]">Reported On</div>
                        <div class="font-bold text-gray-200"><?= Helper::formatDate($report['created_at']) ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500 uppercase text-[10px]">Expiration</div>
                        <div class="font-bold text-gray-200"><?= Helper::formatDate($report['expiration_date']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Private Details & Custody (Visible only to Creator or Admin) -->
            <?php if ($privateDetails && ($isOwner || $isAdmin)): ?>
                <div class="panel border-lime-400/50">
                    <div class="panel-header">
                        <span class="panel-title text-lime-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Confidential Custody &amp; Verification Secrets
                        </span>
                        <span class="font-mono text-xs text-gray-400">Restricted Access</span>
                    </div>

                    <div class="space-y-3 text-xs font-mono">
                        <div>
                            <span class="text-gray-500 uppercase">Custody Status:</span>
                            <span class="text-gray-200 font-bold ml-2"><?= Helper::escape($privateDetails['custody_status'] ?? 'Held by Finder') ?></span>
                        </div>
                        <?php if (!empty($privateDetails['safe_collection_instructions'])): ?>
                            <div>
                                <span class="text-gray-500 uppercase">Safe Collection Instructions:</span>
                                <p class="text-gray-300 mt-0.5 bg-[#111310] p-2 border border-[#2A3026]"><?= Helper::escape($privateDetails['safe_collection_instructions']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($privateDetails['secret_question'])): ?>
                            <div>
                                <span class="text-gray-500 uppercase">Secret Question Asked to Claimants:</span>
                                <p class="text-lime-400 mt-0.5 bg-[#111310] p-2 border border-[#2A3026]"><?= Helper::escape($privateDetails['secret_question']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($privateDetails['secret_answer'])): ?>
                            <div>
                                <span class="text-gray-500 uppercase">Expected Answer (Secret):</span>
                                <p class="text-amber-400 mt-0.5 bg-[#111310] p-2 border border-[#2A3026]"><?= Helper::escape($privateDetails['secret_answer']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Action Box for Visitors / Claimants -->
            <?php if ($report['report_type'] === 'found' && !$isOwner): ?>
                <div class="panel border-lime-400/40">
                    <h2 class="text-lg font-bold mb-2">Is this your item?</h2>
                    <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                        To protect belongings from false claims, TRACE requires claimants to verify ownership through specific non-public identifying details before contact or collection coordinates are granted.
                    </p>

                    <?php if ($report['status'] === 'recovered'): ?>
                        <div class="p-3 bg-green-950/40 border border-green-800 text-green-300 text-xs font-mono">
                            &bull; This item has already been successfully claimed and returned to its rightful owner.
                        </div>
                    <?php elseif ($userHasClaimed): ?>
                        <div class="p-3 bg-blue-950/40 border border-blue-800 text-blue-300 text-xs font-mono">
                            &bull; You have submitted an active claim for this item. Check your <a href="/claims" class="underline font-bold">Claims Dashboard</a>.
                        </div>
                    <?php else: ?>
                        <a href="/reports/<?= $report['id'] ?>/claim" class="btn btn-primary w-full py-3">
                            Submit Ownership Claim &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Correlated Intelligence Matches (Visible to Owner or Admin) -->
            <?php if (($isOwner || $isAdmin) && !empty($matches)): ?>
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                            Smart Matches (<?= count($matches) ?>)
                        </span>
                        <a href="/matches" class="font-mono text-xs text-lime-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($matches as $m): ?>
                            <?php 
                                $isLostReport = ($report['report_type'] === 'lost');
                                $candidateTitle = $isLostReport ? $m['found_title'] : $m['lost_title'];
                                $candidateId = $isLostReport ? $m['found_report_id'] : $m['lost_report_id'];
                                $candidateIdent = $isLostReport ? $m['found_identifier'] : $m['lost_identifier'];
                                $candidateLoc = $isLostReport ? $m['found_location'] : $m['lost_location'];
                            ?>
                            <div class="p-3 bg-[#111310] border border-[#2A3026] text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-mono font-bold text-gray-400"><?= Helper::escape($candidateIdent) ?></span>
                                    <?= Helper::scoreBadge((int)$m['score']) ?>
                                </div>
                                <div class="font-bold text-sm text-gray-200 mb-1"><?= Helper::escape($candidateTitle) ?></div>
                                <div class="text-gray-400 text-[11px] mb-2"><?= Helper::escape($candidateLoc) ?></div>
                                <div class="text-[11px] text-gray-500 italic mb-2 border-l-2 border-lime-400 pl-2">
                                    "<?= Helper::escape($m['explanation']) ?>"
                                </div>
                                <div class="flex justify-end gap-2">
                                    <a href="/reports/<?= $candidateId ?>" class="btn btn-outline btn-sm">Inspect Signal &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Claims Review Queue (Visible to Finder or Admin) -->
            <?php if (($isOwner || $isAdmin) && !empty($claims)): ?>
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Ownership Claims (<?= count($claims) ?>)</span>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($claims as $c): ?>
                            <div class="p-3 bg-[#111310] border border-[#2A3026] text-xs">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-gray-200"><?= Helper::escape($c['claimant_name']) ?></span>
                                    <?= Helper::statusBadge($c['status']) ?>
                                </div>
                                <p class="text-gray-400 text-[11px] mb-2 line-clamp-2"><?= Helper::escape($c['claim_description']) ?></p>
                                <a href="/claims/<?= $c['id'] ?>" class="btn btn-primary btn-sm w-full">Review Evidence &amp; Verification &rarr;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Safety Guidelines -->
            <div class="panel bg-[#111310] text-xs text-gray-400 space-y-2">
                <div class="font-bold text-gray-200 uppercase font-mono tracking-wider">Safety &amp; Handover Protocol</div>
                <ul class="list-disc pl-4 space-y-1">
                    <li>Always conduct physical handovers in monitored public areas (campus reception, library desk, security station).</li>
                    <li>Never disclose home addresses or transfer money to claim items.</li>
                    <li>If an item appears suspicious or hazardous, alert campus security or local authorities immediately.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
