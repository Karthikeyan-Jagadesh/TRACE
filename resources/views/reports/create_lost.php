<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-4xl">
    <div class="mb-8">
        <span class="editorial-eyebrow">Incident Ingestion Protocol</span>
        <h1 class="text-3xl font-extrabold tracking-tight">Report a Lost Item</h1>
        <p class="text-gray-400 text-sm mt-1">Provide clear, accurate details to maximize correlation accuracy across the network.</p>
    </div>

    <form id="report-wizard" action="/reports/create/lost" method="POST" enctype="multipart/form-data" class="panel">
        <?= Helper::csrfField() ?>

        <!-- Wizard Progress Bar -->
        <div class="wizard-progress">
            <div class="wizard-step-tab active" data-step="1">
                <span>01</span>
                <span>Item Identification</span>
            </div>
            <div class="wizard-step-tab" data-step="2">
                <span>02</span>
                <span>Time &amp; Geography</span>
            </div>
            <div class="wizard-step-tab" data-step="3">
                <span>03</span>
                <span>Review &amp; Ingest</span>
            </div>
        </div>

        <!-- Step 1: Identification -->
        <div class="wizard-step-pane" data-step="1">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 1 &mdash; What did you lose?
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group md:col-span-2">
                    <label class="form-label" for="item_title">Item Name / Summary *</label>
                    <input type="text" 
                           id="item_title" 
                           name="item_title" 
                           value="<?= Helper::escape($old['item_title'] ?? '') ?>" 
                           required 
                           class="form-control text-base font-semibold" 
                           placeholder="e.g. Sony WH-1000XM4 Noise-Cancelling Headphones">
                    <div class="form-hint">A concise, descriptive summary of the item</div>
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
                    <label class="form-label" for="brand">Brand / Manufacturer (Optional)</label>
                    <input type="text" 
                           id="brand" 
                           name="brand" 
                           value="<?= Helper::escape($old['brand'] ?? '') ?>" 
                           class="form-control" 
                           placeholder="e.g. Sony, Apple, Dell, Hydro Flask">
                </div>

                <div class="form-group">
                    <label class="form-label" for="color">Primary Color (Optional)</label>
                    <input type="text" 
                           id="color" 
                           name="color" 
                           value="<?= Helper::escape($old['color'] ?? '') ?>" 
                           class="form-control" 
                           placeholder="e.g. Matte Black, Navy Blue, Silver">
                </div>

                <div class="form-group">
                    <label class="form-label" for="image">Photograph (Optional, max 5MB)</label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/jpeg,image/png,image/webp" 
                           class="form-control text-xs file:mr-3 file:py-1 file:px-2 file:border-0 file:bg-lime-400 file:text-black file:font-semibold">
                    <div class="form-hint">Upload a reference photo if you have one</div>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-[#2A3026]">
                <button type="button" data-action="next-step" class="btn btn-primary">
                    Next: Location &amp; Time &rarr;
                </button>
            </div>
        </div>

        <!-- Step 2: Time & Geography -->
        <div class="wizard-step-pane hidden" data-step="2">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 2 &mdash; Where and when was it last seen?
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group md:col-span-2">
                    <label class="form-label" for="general_location">General Location / Facility *</label>
                    <input type="text" 
                           id="general_location" 
                           name="general_location" 
                           value="<?= Helper::escape($old['general_location'] ?? '') ?>" 
                           required 
                           class="form-control" 
                           placeholder="e.g. Central Library, 2nd Floor Study Cubicle">
                    <div class="form-hint">Campus building, room, cafeteria, transit stop, or general vicinity</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="event_date">Approximate Date Lost *</label>
                    <input type="date" 
                           id="event_date" 
                           name="event_date" 
                           value="<?= Helper::escape($old['event_date'] ?? date('Y-m-d')) ?>" 
                           required 
                           class="form-control font-mono">
                </div>

                <div class="form-group">
                    <label class="form-label" for="event_time">Approximate Time (Optional)</label>
                    <input type="time" 
                           id="event_time" 
                           name="event_time" 
                           value="<?= Helper::escape($old['event_time'] ?? '') ?>" 
                           class="form-control font-mono">
                </div>

                <div class="form-group md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="form-label mb-0">Pin Coordinates on Map (Optional)</label>
                        <span class="text-xs text-gray-500 font-mono">Click map to drop marker</span>
                    </div>
                    <div id="trace-map" style="height: 260px;" class="mb-2"></div>
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
                    Next: Description &amp; Submit &rarr;
                </button>
            </div>
        </div>

        <!-- Step 3: Details & Ingest -->
        <div class="wizard-step-pane hidden" data-step="3">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-lime-400"></span>
                Step 3 &mdash; Distinctive details &amp; submission
            </h2>

            <div class="form-group">
                <label class="form-label" for="description">Detailed Description *</label>
                <textarea id="description" 
                          name="description" 
                          required 
                          rows="4" 
                          class="form-control" 
                          placeholder="Describe distinguishing marks, condition, stickers, case color, contents, or circumstances..."><?= Helper::escape($old['description'] ?? '') ?></textarea>
                <div class="form-hint">Our matching engine indexes keywords to evaluate potential matches</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label" for="reward_amount">Optional Reward Offering ($ / ₹)</label>
                    <input type="number" 
                           id="reward_amount" 
                           name="reward_amount" 
                           value="<?= Helper::escape($old['reward_amount'] ?? '') ?>" 
                           step="0.01" 
                           class="form-control font-mono" 
                           placeholder="0.00">
                    <div class="form-hint">Only if permitted by campus/organizational policy</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_preference">Contact Preference</label>
                    <select id="contact_preference" name="contact_preference" class="form-control">
                        <option value="in_app" selected>In-App Verified Messaging &amp; Claims (Recommended)</option>
                        <option value="campus_desk">Campus Security / Reception Desk</option>
                    </select>
                </div>
            </div>

            <div class="p-4 bg-[#111310] border border-[#2A3026] text-xs text-gray-400 mb-6">
                <div class="font-bold text-gray-200 mb-1">Privacy Guarantee</div>
                Your personal phone number and exact personal residence will not be published. Any potential matches will be correlated automatically and displayed in your dashboard.
            </div>

            <div class="flex justify-between mt-6 pt-4 border-t border-[#2A3026]">
                <button type="button" data-action="prev-step" class="btn btn-outline">
                    &larr; Back
                </button>
                <button type="submit" class="btn btn-primary px-8">
                    Register Lost Report &amp; Scan Signals &rarr;
                </button>
            </div>
        </div>
    </form>
</div>
