<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-12">
    <div class="max-w-lg mx-auto">
        <div class="panel">
            <div class="mb-6 text-center">
                <span class="editorial-eyebrow">Identity Registry</span>
                <h1 class="text-2xl font-bold">Register on TRACE</h1>
                <p class="text-xs text-gray-400 mt-1">Join your campus or local community lost &amp; found network</p>
            </div>

            <form action="/register" method="POST">
                <?= Helper::csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= Helper::escape($old['name'] ?? '') ?>" 
                           required 
                           class="form-control" 
                           placeholder="Alex Morgan">
                    <?php if (!empty($errors['name'])): ?>
                        <div class="form-error"><?= Helper::escape($errors['name'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Institutional or Personal Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= Helper::escape($old['email'] ?? '') ?>" 
                           required 
                           class="form-control" 
                           placeholder="alex@university.edu">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="form-error"><?= Helper::escape($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="organization_id">Campus / Organization (Optional)</label>
                    <select id="organization_id" name="organization_id" class="form-control">
                        <option value="">-- Independent Community Member --</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?= $org['id'] ?>" <?= (($old['organization_id'] ?? '') == $org['id']) ? 'selected' : '' ?>>
                                <?= Helper::escape($org['name']) ?> (<?= Helper::escape($org['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="password">Password (Min. 8 chars)</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="form-control" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                        <?php if (!empty($errors['password'])): ?>
                            <div class="form-error"><?= Helper::escape($errors['password'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required 
                               class="form-control" 
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                        <?php if (!empty($errors['password_confirmation'])): ?>
                            <div class="form-error"><?= Helper::escape($errors['password_confirmation'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3 mt-4">
                    Create Network Profile &rarr;
                </button>
            </form>

            <div class="border-t border-[#2A3026] mt-6 pt-4 text-center text-xs text-gray-400">
                Already registered? 
                <a href="/login" class="text-lime-400 font-semibold hover:underline">Sign in to your account</a>
            </div>
        </div>
    </div>
</div>
