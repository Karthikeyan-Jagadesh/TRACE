<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-3xl">
    <div class="mb-8 pb-4 border-b border-[#2A3026]">
        <span class="editorial-eyebrow">Terminal Preferences</span>
        <h1 class="text-3xl font-extrabold tracking-tight">Account Settings</h1>
        <p class="text-gray-400 text-sm mt-1">Configure your personal identity profile, theme, and security credentials</p>
    </div>

    <div class="space-y-8">
        <!-- Profile & Preferences Form -->
        <form action="/settings/profile" method="POST" class="panel">
            <?= Helper::csrfField() ?>
            <div class="panel-header">
                <span class="panel-title">Profile &amp; Visual Preferences</span>
            </div>

            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label" for="name">Full Display Name</label>
                    <input type="text" id="name" name="name" value="<?= Helper::escape($user['name']) ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" value="<?= Helper::escape($user['email']) ?>" readonly class="form-control font-mono opacity-70 cursor-not-allowed">
                    <div class="form-hint">Institutional email cannot be modified directly</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="organization_id">Campus / Organization</label>
                    <select id="organization_id" name="organization_id" class="form-control">
                        <option value="">-- Independent Member --</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?= $org['id'] ?>" <?= ($user['organization_id'] == $org['id']) ? 'selected' : '' ?>>
                                <?= Helper::escape($org['name']) ?> (<?= Helper::escape($org['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="form-group">
                        <label class="form-label" for="theme">Default Theme Interface</label>
                        <select id="theme" name="theme" class="form-control font-mono text-xs">
                            <option value="dark" <?= ($prefs['theme'] === 'dark') ? 'selected' : '' ?>>Dark Obsidian (#111310)</option>
                            <option value="light" <?= ($prefs['theme'] === 'light') ? 'selected' : '' ?>>Warm Ivory (#F4F2EA)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notification Channels</label>
                        <div class="space-y-2 text-xs pt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="in_app_notifications" value="1" <?= $prefs['in_app_notifications'] ? 'checked' : '' ?> class="accent-lime-400">
                                <span>In-App Real-Time Alerts</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="email_notifications" value="1" <?= $prefs['email_notifications'] ? 'checked' : '' ?> class="accent-lime-400">
                                <span>Email Match Summaries</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 mt-6 border-t border-[#2A3026]">
                <button type="submit" class="btn btn-primary btn-sm px-6">
                    Save Profile Changes
                </button>
            </div>
        </form>

        <!-- Password Change Form -->
        <form action="/settings/password" method="POST" class="panel">
            <?= Helper::csrfField() ?>
            <div class="panel-header">
                <span class="panel-title">Security &amp; Credentials</span>
            </div>

            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password (Min. 8 chars)</label>
                        <input type="password" id="new_password" name="new_password" required class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 mt-6 border-t border-[#2A3026]">
                <button type="submit" class="btn btn-outline btn-sm px-6">
                    Update Security Password
                </button>
            </div>
        </form>
    </div>
</div>
