<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8 max-w-3xl">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Real-Time Dispatch</span>
            <h1 class="text-3xl font-extrabold tracking-tight">Notifications Log</h1>
            <p class="text-gray-400 text-sm mt-1">Live correlation alerts, claim updates, and lifecycle dispatches</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <form action="/notifications/read-all" method="POST">
                <?= Helper::csrfField() ?>
                <button type="submit" class="btn btn-outline btn-sm font-mono text-lime-400">
                    Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="panel text-center py-16 text-gray-500 text-sm">
            You have no notifications in your dispatch log.
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($notifications as $n): ?>
                <div class="panel p-4 flex items-center justify-between gap-4 <?= $n['is_read'] ? 'opacity-60 bg-[#111310]' : 'border-lime-400/40 bg-[#1B1E19]' ?>">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[11px] font-bold uppercase text-lime-400"><?= Helper::escape($n['event_type']) ?></span>
                            <span class="text-xs text-gray-500 font-mono">&bull; <?= Helper::timeAgo($n['created_at']) ?></span>
                        </div>
                        <h2 class="text-sm font-bold text-gray-100"><?= Helper::escape($n['title']) ?></h2>
                        <p class="text-xs text-gray-300"><?= Helper::escape($n['message']) ?></p>
                        <?php if (!empty($n['link_url'])): ?>
                            <a href="<?= Helper::escape($n['link_url']) ?>" class="text-xs text-lime-400 font-mono hover:underline inline-block pt-1">
                                Open Signal &rarr;
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!$n['is_read']): ?>
                        <form action="/notifications/<?= $n['id'] ?>/read" method="POST">
                            <?= Helper::csrfField() ?>
                            <button type="submit" class="btn btn-outline btn-sm text-[11px] font-mono">
                                Mark Read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
