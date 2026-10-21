<?php
use App\Helpers\Helper;
?>
<header class="trace-header">
    <div class="trace-container">
        <div class="nav-inner">
            <!-- Brand -->
            <a href="/" class="trace-brand">
                <span class="brand-symbol">T</span>
                <span class="brand-text">TRACE</span>
                <span class="brand-tag">v1.0.0</span>
            </a>

            <!-- Navigation Links -->
            <nav>
                <ul class="nav-links">
                    <li><a href="/search" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/search') ? 'active' : '' ?>">Discover</a></li>
                    <li><a href="/map" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/map') ? 'active' : '' ?>">Spatial Map</a></li>
                    <li><a href="/#how-it-works" class="nav-link">How It Works</a></li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li><a href="/dashboard" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/dashboard') ? 'active' : '' ?>">Dashboard</a></li>
                        <li>
                            <a href="/matches" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/matches') ? 'active' : '' ?>">
                                Matches
                            </a>
                        </li>
                        <li><a href="/claims" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/claims') ? 'active' : '' ?>">Claims</a></li>
                        <?php if ($isAdmin): ?>
                            <li><a href="/admin" class="nav-link text-lime-400 <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin') ? 'active' : '' ?>">Admin</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Actions & User Profile -->
            <div class="nav-actions">
                <!-- Theme Toggle Button -->
                <button type="button" class="btn btn-outline btn-sm" data-action="toggle-theme" title="Toggle Theme (Dark / Light)" aria-label="Toggle Theme">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                <?php if ($isLoggedIn): ?>
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button type="button" id="notif-trigger" class="btn btn-outline btn-sm relative" aria-label="Notifications">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <?php if ($unreadCount > 0): ?>
                                <span id="notif-badge" class="absolute -top-1 -right-1 bg-lime-400 text-black text-[10px] font-mono font-bold px-1.5 py-0.2 rounded-full"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-[#1B1E19] border border-[#2A3026] shadow-xl z-50 p-2 text-xs">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-[#2A3026] px-2">
                                <span class="font-mono uppercase font-bold text-gray-300">Live Signals</span>
                                <a href="/notifications" class="text-lime-400 hover:underline">View All</a>
                            </div>
                            <div class="max-h-64 overflow-y-auto space-y-1">
                                <?php
                                $recentNotifs = \App\Repositories\NotificationRepository::getByUser($authUser['id'], 5);
                                if (empty($recentNotifs)): ?>
                                    <div class="p-3 text-center text-gray-500">No recent notifications</div>
                                <?php else: ?>
                                    <?php foreach ($recentNotifs as $n): ?>
                                        <div class="p-2 border-b border-[#252A22] notif-item <?= $n['is_read'] ? 'opacity-50' : '' ?>">
                                            <a href="<?= Helper::escape($n['link_url'] ?: '/notifications') ?>" class="block font-medium text-gray-200 hover:text-lime-400"><?= Helper::escape($n['title']) ?></a>
                                            <div class="text-[11px] text-gray-400 mt-0.5"><?= Helper::escape($n['message']) ?></div>
                                            <div class="flex items-center justify-between mt-1 text-[10px] text-gray-500 font-mono">
                                                <span><?= Helper::timeAgo($n['created_at']) ?></span>
                                                <?php if (!$n['is_read']): ?>
                                                    <button type="button" data-action="mark-read" data-id="<?= $n['id'] ?>" class="text-lime-400 hover:underline">Mark read</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Account Menu -->
                    <div class="flex items-center gap-2">
                        <a href="/dashboard" class="btn btn-outline btn-sm font-mono">
                            <?= Helper::escape(explode(' ', $authUser['name'])[0]) ?>
                        </a>
                        <form action="/logout" method="POST" class="inline">
                            <?= Helper::csrfField() ?>
                            <button type="submit" class="btn btn-outline btn-sm text-red-400 hover:border-red-400" title="Sign Out">
                                Sign Out
                            </button>
                        </form>
                    </div>

                    <!-- Primary Action -->
                    <a href="/reports/create/lost" class="btn btn-primary btn-sm">
                        + Report Item
                    </a>

                <?php else: ?>
                    <a href="/login" class="nav-link">Sign In</a>
                    <a href="/register" class="btn btn-outline btn-sm">Register</a>
                    <a href="/reports/create/lost" class="btn btn-primary btn-sm">
                        + Report Item
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
