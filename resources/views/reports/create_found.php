<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-4xl">
    <div class="mb-8">
        <span class="editorial-eyebrow">Custody Ingestion Protocol</span>
        <h1 class="text-3xl font-extrabold tracking-tight">Report a Found Item</h1>
        <p class="text-gray-400 text-sm mt-1">Safeguard belongings with private verification questions to prevent fraudulent claims.</p>
    </div>

    <form id="report-wizard" action="/reports/create/found" method="POST" enctype="multipart/form-data" class="panel">
        <?= Helper::csrfField() ?>

        <!-- Wizard Progress Bar -->
        <div class="wizard-progress">
            <div class="wizard-step-tab active" data-step="1">
                <span>01</span>
                <span>Public Description</span>
            </div>
            <div class="wizard-step-tab" data-step="2">
                <span>02</span>
                <span>Found Location &amp; Custody</span>
            </div>
            <div class="wizard-step-tab" data-step="3">
                <span>03</span>
                <span>Secret Verification</span>
            </div>
        </div>

        <!-- Step 1: Identification -->
        <div class="wizard-step-pane" data-step="1">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 1 &mdash; What did you find?
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group md:col-span-2">
                    <label class="form-label" for="item_title">Public Title / Summary *</label>
                    <input type="text" 
                           id="item_title" 
                           name="item_title" 
                           value="<?= Helper::escape($old['item_title'] ?? '') ?>" 
                           required 
                           class="form-control text-base font-semibold" 
                           placeholder="e.g. Wireless Earbuds in Black Charging Case">
                    <div class="form-hint">Keep the title general enough that only the rightful owner knows specific hidden features</div>
                    <?php if (!empty($errors['item_title'])): ?>
                        <div class="form-error"><?= Helper::escape($errors['item_title'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">Category *</label>
                    <select id="category" name="category" required class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= (($old['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="brand">Brand / Make (Optional)</label>
                    <input type="text" 
                           id="brand" 
                           name="brand" 
                           value="<?= Helper::escape($old['brand'] ?? '') ?>" 
                           class="form-control" 
                           placeholder="e.g. Apple, Samsung, Anker, Casio">
                </div>

                <div class="form-group">
                    <label class="form-label" for="color">Color (Optional)</label>
                    <input type="text" 
                           id="color" 
                           name="color" 
                           value="<?= Helper::escape($old['color'] ?? '') ?>" 
                           class="form-control" 
                           placeholder="e.g. White, Silver, Dark Blue">
                </div>

                <div class="form-group">
                    <label class="form-label" for="image">Photograph (Optional, max 5MB)</label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/jpeg,image/png,image/webp" 
                           class="form-control text-xs file:mr-3 file:py-1 file:px-2 file:border-0 file:bg-lime-400 file:text-black file:font-semibold">
                    <div class="form-hint">Tip: Avoid photographing distinctive stickers or serial numbers so they can be used for verification</div>
                </div>

                <div class="form-group md:col-span-2">
                    <label class="form-label" for="description">Public Description *</label>
                    <textarea id="description" 
                              name="description" 
                              required 
                              rows="3" 
                              class="form-control" 
                              placeholder="General description visible to the public..."><?= Helper::escape($old['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-[#2A3026]">
                <button type="button" data-action="next-step" class="btn btn-primary">
                    Next: Location &amp; Custody &rarr;
                </button>
            </div>
        </div>

        <!-- Step 2: Location & Custody -->
        <div class="wizard-step-pane hidden" data-step="2">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 2 &mdash; Where was it found &amp; who currently holds it?
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group md:col-span-2">
                    <label class="form-label" for="general_location">General Location Where Found *</label>
                    <input type="text" 
                           id="general_location" 
                           name="general_location" 
                           value="<?= Helper::escape($old['general_location'] ?? '') ?>" 
                           required 
                           class="form-control" 
                           placeholder="e.g. Student Cafeteria, East Lounge Booth">
                </div>

                <div class="form-group">
                    <label class="form-label" for="event_date">Date Found *</label>
                    <input type="date" 
                           id="event_date" 
                           name="event_date" 
                           value="<?= Helper::escape($old['event_date'] ?? date('Y-m-d')) ?>" 
                           required 
                           class="form-control font-mono">
                </div>

                <div class="form-group">
                    <label class="form-label" for="custody_status">Current Custody Status</label>
                    <select id="custody_status" name="custody_status" class="form-control">
                        <option value="Held by Finder">In my personal possession</option>
                        <option value="Handed to Security Desk">Handed over to Campus Security Desk</option>
                        <option value="Left at Reception / Helpdesk">Left at Facility Reception Desk</option>
                        <option value="Department Office">Department / Administration Office</option>
                    </select>
                </div>

                <div class="form-group md:col-span-2">
                    <label class="form-label" for="safe_collection_instructions">Safe Collection Instructions</label>
                    <input type="text" 
                           id="safe_collection_instructions" 
                           name="safe_collection_instructions" 
                           value="<?= Helper::escape($old['safe_collection_instructions'] ?? '') ?>" 
                           class="form-control" 
                           placeholder="e.g. Can meet near Main Gate Security during lunch, or ask for Item #4 at Library Reception">
                </div>

                <div class="form-group md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="form-label mb-0">Pin Coordinates on Map (Optional)</label>
                        <span class="text-xs text-gray-500 font-mono">Click map to drop marker</span>
                    </div>
                    <div id="trace-map" style="height: 240px;" class="mb-2"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="report-lat" name="latitude" placeholder="Latitude" value="<?= Helper::escape($old['latitude'] ?? '') ?>" readonly class="form-control font-mono text-xs">
                        <input type="text" id="report-lng" name="longitude" placeholder="Longitude" value="<?= Helper::escape($old['longitude'] ?? '') ?>" readonly class="form-control font-mono text-xs">
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6 pt-4 border-t border-[#2A3026]">
                <button type="button" data-action="prev-step" class="btn btn-outline">
                    &larr; Back
                </button>
                <button type="button" data-action="next-step" class="btn btn-primary">
                    Next: Secret Verification &rarr;
                </button>
            </div>
        </div>

        <!-- Step 3: Secret Ownership Verification -->
        <div class="wizard-step-pane hidden" data-step="3">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 3 &mdash; Secret Ownership Verification (Crucial Anti-Fraud)
            </h2>

            <div class="verification-callout">
                <div class="flex items-center gap-2 font-bold text-lime-400 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    CONFIDENTIALITY GUARANTEE
                </div>
                <p class="text-xs text-gray-300 leading-relaxed">
                    The question and answer you enter below will <strong>NEVER</strong> be exposed publicly, included in search results, or revealed in the page source. When a person claims this item, they will be prompted to answer this question to prove ownership.
                </p>
            </div>

            <div class="form-group">
                <label class="form-label" for="secret_question">Verification Question *</label>
                <input type="text" 
                       id="secret_question" 
                       name="secret_question" 
                       value="<?= Helper::escape($old['secret_question'] ?? '') ?>" 
                       required 
                       class="form-control font-medium" 
                       placeholder="e.g. What sticker is on the laptop lid? / What is written on the keychain?">
                <div class="form-hint">Prompt the claimant for a detail that only the rightful owner would know</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="secret_answer">Confidential Expected Answer *</label>
                <textarea id="secret_answer" 
                          name="secret_answer" 
                          required 
                          rows="3" 
                          class="form-control font-mono text-sm" 
                          placeholder="e.g. Green NASA circular sticker with minor scratch on upper corner"><?= Helper::escape($old['secret_answer'] ?? '') ?></textarea>
                <div class="form-hint">Stored securely for comparison when reviewing incoming claims</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="secret_notes">Private Notes for Yourself / Campus Staff (Optional)</label>
                <input type="text" 
                       id="secret_notes" 
                       name="secret_notes" 
                       value="<?= Helper::escape($old['secret_notes'] ?? '') ?>" 
                       class="form-control" 
                       placeholder="e.g. Placed in Locker #12, Security Log Sheet #44">
            </div>

            <div class="flex justify-between mt-6 pt-4 border-t border-[#2A3026]">
                <button type="button" data-action="prev-step" class="btn btn-outline">
                    &larr; Back
                </button>
                <button type="submit" class="btn btn-primary px-8">
                    Register Found Item &amp; Ingest Signal &rarr;
                </button>
            </div>
        </div>
    </form>
</div>
