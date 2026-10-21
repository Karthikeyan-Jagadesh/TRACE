<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-2xl">
    <div class="mb-6 pb-4 border-b border-[#2A3026]">
        <span class="editorial-eyebrow">Claim Submission Protocol</span>
        <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Submit Ownership Claim</h1>
        <p class="text-gray-400 text-sm mt-1">
            Claiming: <strong class="text-gray-200"><?= Helper::escape($report['item_title']) ?></strong> (<?= Helper::escape($report['identifier']) ?>)
        </p>
    </div>

    <form action="/claims" method="POST" class="panel">
        <?= Helper::csrfField() ?>
        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">

        <!-- Found Item Reference Card -->
        <div class="flex items-center gap-3 p-3 bg-[#111310] border border-[#2A3026] mb-6">
            <img src="<?= Helper::uploadUrl($report['primary_image']) ?>" class="w-14 h-14 object-cover border border-[#2A3026]">
            <div>
                <span class="font-mono text-xs text-lime-400 font-bold"><?= Helper::escape($report['identifier']) ?></span>
                <div class="font-bold text-sm text-gray-200"><?= Helper::escape($report['item_title']) ?></div>
                <div class="text-xs text-gray-400 font-mono"><?= Helper::escape($report['general_location']) ?> &bull; <?= Helper::formatDate($report['event_date']) ?></div>
            </div>
        </div>

        <!-- Secret Verification Prompt if Configured by Finder -->
        <?php if (!empty($privateDetails['secret_question'])): ?>
            <div class="verification-callout mb-6">
                <div class="flex items-center gap-2 font-bold text-lime-400 text-xs mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    FINDER'S VERIFICATION QUESTION:
                </div>
                <p class="text-sm font-semibold text-gray-100 mb-2">
                    "<?= Helper::escape($privateDetails['secret_question']) ?>"
                </p>
                <div class="text-[11px] text-gray-400">
                    The finder has designated this identifying detail to confirm legitimate ownership. Answer precisely in the evidence box below.
                </div>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <div class="form-group">
                <label class="form-label" for="claim_description">Circumstances of Loss *</label>
                <textarea id="claim_description" 
                          name="claim_description" 
                          required 
                          rows="3" 
                          class="form-control" 
                          placeholder="Explain when and where you lost this item, along with any relevant context..."><?= Helper::escape($old['claim_description'] ?? '') ?></textarea>
                <?php if (!empty($errors['claim_description'])): ?>
                    <div class="form-error"><?= Helper::escape($errors['claim_description'][0]) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="ownership_evidence">Ownership Proof &amp; Verification Answer *</label>
                <textarea id="ownership_evidence" 
                          name="ownership_evidence" 
                          required 
                          rows="4" 
                          class="form-control font-medium" 
                          placeholder="Provide specific proof: answer to verification question, distinctive scratches, stickers, wallpaper, serial number snippet, or purchase date..."><?= Helper::escape($old['ownership_evidence'] ?? '') ?></textarea>
                <div class="form-hint">This evidence will be evaluated by the finder or campus administrator</div>
                <?php if (!empty($errors['ownership_evidence'])): ?>
                    <div class="form-error"><?= Helper::escape($errors['ownership_evidence'][0]) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-3 bg-[#111310] border border-[#2A3026] text-[11px] text-gray-400 my-4">
            <strong>Accountability Notice:</strong> Every claim submission is logged with timestamp, user identity, and network IP. Fraudulent claim attempts result in immediate account suspension.
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-[#2A3026]">
            <a href="/reports/<?= $report['id'] ?>" class="btn btn-outline btn-sm">&larr; Cancel</a>
            <button type="submit" class="btn btn-primary px-8">Submit Verification Claim &rarr;</button>
        </div>
    </form>
</div>
