<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-4xl">
    <!-- Top Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-[#2A3026]">
        <div class="flex items-center gap-3">
            <a href="/claims" class="text-xs font-mono text-gray-400 hover:text-lime-400">&larr; Back to Claims</a>
            <span class="text-gray-600">/</span>
            <span class="font-mono text-xs text-lime-400 font-bold">CLAIM #<?= $claim['id'] ?></span>
        </div>
        <div>
            <?= Helper::statusBadge($claim['status']) ?>
        </div>
    </div>

    <!-- State Machine Progression Tracker -->
    <div class="panel mb-8 p-4 bg-[#1B1E19]">
        <div class="text-[10px] uppercase font-mono text-gray-400 tracking-wider mb-3">Verification State Machine</div>
        <div class="grid grid-cols-4 gap-2 text-center font-mono text-xs">
            <?php
            $states = ['submitted' => '01 Submitted', 'under_review' => '02 Under Review', 'approved' => '03 Approved', 'completed' => '04 Recovered'];
            $statusOrder = ['submitted' => 1, 'info_requested' => 2, 'under_review' => 2, 'approved' => 3, 'completed' => 4, 'rejected' => 0, 'cancelled' => 0];
            $currentLevel = $statusOrder[$claim['status']] ?? 1;
            ?>
            <?php foreach ($states as $sKey => $sLabel): ?>
                <?php 
                    $level = $statusOrder[$sKey];
                    $isPassed = ($currentLevel >= $level && !in_array($claim['status'], ['rejected', 'cancelled']));
                    $isCurrent = ($claim['status'] === $sKey);
                ?>
                <div class="p-2 border <?= $isCurrent ? 'border-lime-400 bg-lime-400/10 text-lime-400 font-bold' : ($isPassed ? 'border-gray-600 text-gray-300' : 'border-[#2A3026] text-gray-600') ?>">
                    <?= $sLabel ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($claim['status'] === 'rejected'): ?>
            <div class="mt-3 p-2 bg-red-950/40 border border-red-800 text-red-300 text-xs text-center font-mono">
                &bull; Claim status: REJECTED &mdash; Evidence could not be verified by finder.
            </div>
        <?php elseif ($claim['status'] === 'info_requested'): ?>
            <div class="mt-3 p-2 bg-amber-950/40 border border-amber-800 text-amber-300 text-xs text-center font-mono">
                &bull; Additional Information Requested &mdash; Please submit supplementary proof below.
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Claim & Item Details (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Item Info -->
            <div class="panel">
                <div class="flex items-center gap-4 mb-4">
                    <img src="<?= Helper::uploadUrl($claim['report_image']) ?>" class="w-16 h-16 object-cover border border-[#2A3026]">
                    <div>
                        <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($claim['report_identifier']) ?></span>
                        <h2 class="text-lg font-bold text-gray-100"><?= Helper::escape($claim['report_title']) ?></h2>
                        <div class="text-xs text-gray-400 font-mono"><?= Helper::escape($claim['report_location']) ?></div>
                    </div>
                </div>
                <a href="/reports/<?= $claim['report_id'] ?>" class="text-xs font-mono text-lime-400 hover:underline">View Parent Found Report &rarr;</a>
            </div>

            <!-- Claim Evidence -->
            <div class="panel space-y-4">
                <div class="panel-header">
                    <span class="panel-title">Claimant Statement &amp; Proof</span>
                    <span class="font-mono text-xs text-gray-400">By <?= Helper::escape($claim['claimant_name']) ?></span>
                </div>

                <div>
                    <span class="text-xs font-mono uppercase text-gray-500">Circumstances of Loss:</span>
                    <div class="p-3 bg-[#111310] border border-[#2A3026] text-xs text-gray-300 mt-1 whitespace-pre-line">
                        <?= Helper::escape($claim['claim_description']) ?>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-mono uppercase text-gray-500">Ownership Evidence &amp; Verification Answer:</span>
                    <div class="p-3 bg-[#111310] border border-lime-400/30 text-xs text-gray-200 mt-1 whitespace-pre-line font-medium">
                        <?= Helper::escape($claim['ownership_evidence']) ?>
                    </div>
                </div>

                <?php if (!empty($claim['return_instructions'])): ?>
                    <div class="p-3 bg-[#111310] border border-green-500/40 text-xs">
                        <span class="font-bold text-green-400 uppercase font-mono block mb-1">Approved Collection Instructions:</span>
                        <p class="text-gray-200"><?= Helper::escape($claim['return_instructions']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- State Transition Audit History -->
            <?php if (!empty($history)): ?>
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Audit Transition Trail</span>
                    </div>
                    <div class="space-y-3 font-mono text-xs">
                        <?php foreach ($history as $h): ?>
                            <div class="p-2.5 bg-[#111310] border border-[#2A3026] flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-lime-400 uppercase"><?= Helper::escape($h['new_status']) ?></span>
                                    <span class="text-gray-500 text-[11px] ml-2">by <?= Helper::escape($h['changer_name']) ?></span>
                                    <?php if (!empty($h['note'])): ?>
                                        <p class="text-gray-400 text-[11px] mt-0.5"><?= Helper::escape($h['note']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[10px] text-gray-500"><?= Helper::formatDate($h['created_at'], 'M d H:i') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Actions & Verification Review (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Review Actions (For Finder or Admin) -->
            <?php if ($isFinder || $isAdmin): ?>
                <div class="panel border-lime-400/50">
                    <div class="panel-header">
                        <span class="panel-title text-lime-400">Review &amp; Verify Ownership</span>
                    </div>

                    <!-- Secret Answer Comparison Box -->
                    <?php if ($privateDetails && !empty($privateDetails['secret_answer'])): ?>
                        <div class="p-3 bg-[#111310] border border-amber-500/40 mb-4 text-xs font-mono">
                            <div class="text-[10px] text-amber-400 uppercase font-bold mb-1">Secret Answer (Finder Expected):</div>
                            <div class="text-gray-200"><?= Helper::escape($privateDetails['secret_answer']) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="/claims/<?= $claim['id'] ?>/review" method="POST" class="space-y-4">
                        <?= Helper::csrfField() ?>

                        <div class="form-group">
                            <label class="form-label text-xs" for="action">Decision Action</label>
                            <select id="action" name="action" class="form-control text-xs font-mono" required>
                                <option value="">-- Select Verification Action --</option>
                                <option value="approve" <?= ($claim['status'] === 'submitted' || $claim['status'] === 'under_review') ? 'selected' : '' ?>>Approve Claim (Valid Proof)</option>
                                <option value="request_info">Request Additional Proof</option>
                                <option value="reject">Reject Claim (Mismatch / Fraud)</option>
                                <option value="complete" <?= ($claim['status'] === 'approved') ? 'selected' : '' ?>>Confirm Handover &amp; Complete Recovery</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label text-xs" for="return_instructions">Safe Collection / Return Instructions (Provided to claimant upon approval)</label>
                            <textarea id="return_instructions" name="return_instructions" rows="2" class="form-control text-xs" placeholder="e.g. Please collect from Library 1st Floor Helpdesk between 10am-4pm..."><?= Helper::escape($claim['return_instructions'] ?? $privateDetails['safe_collection_instructions'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label text-xs" for="note">Reviewer Note</label>
                            <input type="text" id="note" name="note" class="form-control text-xs" placeholder="Optional audit comment">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-full py-2.5">
                            Execute Decision &rarr;
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Confirmation Action for Claimant when Approved -->
            <?php if ($isClaimant && $claim['status'] === 'approved'): ?>
                <div class="panel border-green-500/50">
                    <h3 class="font-bold text-green-400 mb-2">Claim Approved!</h3>
                    <p class="text-xs text-gray-300 leading-relaxed mb-4">
                        Your ownership proof was verified. Once you have received your item from the finder or collection desk, confirm handover below to conclude the report lifecycle.
                    </p>
                    <form action="/claims/<?= $claim['id'] ?>/review" method="POST">
                        <?= Helper::csrfField() ?>
                        <input type="hidden" name="action" value="complete">
                        <input type="hidden" name="note" value="Claimant confirmed successful physical recovery">
                        <button type="submit" class="btn btn-primary w-full py-3">
                            Confirm Item Recovered &amp; Close Case &check;
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Case Summary -->
            <div class="panel text-xs font-mono space-y-2 text-gray-400">
                <div class="text-gray-200 font-bold uppercase tracking-wider">Case Registry</div>
                <div>Submitted: <?= Helper::formatDate($claim['created_at']) ?></div>
                <div>Claimant: <?= Helper::escape($claim['claimant_name']) ?></div>
                <div>Reviewer: <?= Helper::escape($claim['reviewer_name'] ?? 'Pending assignment') ?></div>
            </div>
        </div>
    </div>
</div>
