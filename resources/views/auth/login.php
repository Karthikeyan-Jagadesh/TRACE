<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-12">
    <div class="max-w-md mx-auto">
        <div class="panel">
            <div class="mb-6 text-center">
                <span class="editorial-eyebrow">Secure Identification</span>
                <h1 class="text-2xl font-bold">Sign In to TRACE</h1>
                <p class="text-xs text-gray-400 mt-1">Access your lost reports, potential matches, and verified claims</p>
            </div>

            <form action="/login" method="POST">
                <?= Helper::csrfField() ?>
                <input type="hidden" name="return" value="<?= Helper::escape($returnUrl ?? '/dashboard') ?>">

                <div class="form-group">
                    <label class="form-label" for="email">Institutional or Personal Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= Helper::escape($oldEmail ?? '') ?>" 
                           required 
                           class="form-control" 
                           placeholder="you@campus.edu or name@example.com"
                           autofocus>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="form-error"><?= Helper::escape($errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="flex justify-between items-center mb-1">
                        <label class="form-label mb-0" for="password">Password</label>
                        <span class="text-xs text-gray-500 font-mono">Confidential</span>
                    </div>
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

                <button type="submit" class="btn btn-primary w-full py-3 mt-4">
                    Authenticate &rarr;
                </button>
            </form>

            <div class="border-t border-[#2A3026] mt-6 pt-4 text-center text-xs text-gray-400">
                Don't have an account yet? 
                <a href="/register" class="text-lime-400 font-semibold hover:underline">Register your profile</a>
            </div>
        </div>
    </div>
</div>
