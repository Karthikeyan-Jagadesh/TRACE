<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Identity Registry</span>
            <h1 class="text-3xl font-extrabold tracking-tight">User Management (<?= $total ?>)</h1>
            <p class="text-gray-400 text-sm mt-1">Platform user accounts, institutional affiliations, and status suspension</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="/admin" class="btn btn-outline btn-sm font-mono">&larr; Back to Admin Overview</a>
        </div>
    </div>

    <div class="panel p-0 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#111310] font-mono border-b border-[#2A3026] text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="p-3">User ID</th>
                    <th class="p-3">Name &amp; Email</th>
                    <th class="p-3">Organization</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Activity</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Account Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3026]">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-[#111310]/50">
                        <td class="p-3 font-mono font-bold text-gray-400">#<?= $u['id'] ?></td>
                        <td class="p-3">
                            <span class="font-bold text-gray-200"><?= Helper::escape($u['name']) ?></span>
                            <span class="font-mono text-gray-500 text-[10px] block"><?= Helper::escape($u['email']) ?></span>
                        </td>
                        <td class="p-3 font-mono text-gray-300"><?= Helper::escape($u['organization_name'] ?? 'Independent') ?></td>
                        <td class="p-3 font-mono">
                            <span class="px-2 py-0.5 border <?= ($u['role'] === 'admin') ? 'border-lime-400 text-lime-400 bg-lime-400/10' : 'border-[#2A3026] text-gray-400' ?>">
                                <?= strtoupper($u['role']) ?>
                            </span>
                        </td>
                        <td class="p-3 font-mono text-gray-400">
                            <?= $u['report_count'] ?> reports &bull; <?= $u['claim_count'] ?> claims
                        </td>
                        <td class="p-3 font-mono">
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="text-green-400 font-bold">&bull; Active</span>
                            <?php else: ?>
                                <span class="text-red-400 font-bold">&bull; Suspended</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-right">
                            <?php if ($u['id'] !== $authUser['id']): ?>
                                <form action="/admin/users/<?= $u['id'] ?>/status" method="POST" class="inline">
                                    <?= Helper::csrfField() ?>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="btn btn-outline btn-sm text-red-400 hover:border-red-400">
                                            Suspend Account
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-outline btn-sm text-lime-400 hover:border-lime-400">
                                            Activate Account
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <span class="text-gray-500 font-mono text-[11px]">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
