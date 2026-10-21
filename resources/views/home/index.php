<?php
use App\Helpers\Helper;
?>

<!-- Section A: Editorial Hero -->
<section class="py-12 lg:py-16 border-b border-[#2A3026]">
    <div class="trace-container">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Copy -->
            <div class="lg:col-span-7">
                <span class="editorial-eyebrow">Community-Powered Recovery Network</span>
                <h1 class="editorial-title mb-6">
                    Lost somewhere.<br>
                    Found through <span class="text-lime-400">TRACE.</span>
                </h1>
                <p class="editorial-lead mb-8">
                    Report a missing belonging, discover what others have found, and turn scattered clues into meaningful connections.
                </p>

                <!-- Search Bar -->
                <form action="/search" method="GET" class="mb-8 max-w-xl">
                    <div class="flex items-center border border-[#2A3026] bg-[#1B1E19] focus-within:border-lime-400 transition-colors">
                        <span class="pl-4 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" 
                               name="q" 
                               placeholder="Search items, places, or descriptions..." 
                               class="w-full bg-transparent border-0 px-4 py-3.5 text-sm focus:outline-none text-[#F4F2EA]">
                        <button type="submit" class="btn btn-primary px-6">
                            Scan
                        </button>
                    </div>
                </form>

                <!-- Hero CTAs -->
                <div class="flex flex-wrap items-center gap-4">
                    <a href="/reports/create/lost" class="btn btn-primary btn-lg">
                        Report an Item
                    </a>
                    <a href="/search?type=found" class="btn btn-outline btn-lg">
                        Explore Found Items &rarr;
                    </a>
                </div>
            </div>

            <!-- Hero Visual Composition: Spatial Editorial Signal Card -->
            <div class="lg:col-span-5">
                <div class="panel border-lime-400/40 relative shadow-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#2A3026] text-xs font-mono">
                        <span class="text-lime-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-lime-400 animate-ping"></span>
                            ACTIVE CORRELATION
                        </span>
                        <span class="text-gray-400">SIGNAL ID #TRC-8921</span>
                    </div>

                    <!-- Side-by-Side Correlated Reports -->
                    <div class="space-y-3 mb-4">
                        <div class="p-3 bg-[#111310] border border-[#2A3026]">
                            <div class="flex justify-between items-center text-xs font-mono mb-1">
                                <span class="text-amber-400 font-bold">LOST REPORT</span>
                                <span class="text-gray-500">Yesterday, 14:20</span>
                            </div>
                            <div class="font-bold text-sm text-gray-200">Space Grey MacBook Air M2</div>
                            <div class="text-xs text-gray-400 mt-0.5">Central Library &bull; Study Booth 4</div>
                        </div>

                        <div class="flex justify-center -my-2 relative z-10">
                            <span class="font-mono text-xs px-3 py-1 bg-lime-400 text-black font-bold border border-black shadow">
                                94% SIGNAL MATCH
                            </span>
                        </div>

                        <div class="p-3 bg-[#111310] border border-lime-400/30">
                            <div class="flex justify-between items-center text-xs font-mono mb-1">
                                <span class="text-lime-400 font-bold">FOUND REPORT</span>
                                <span class="text-gray-500">Yesterday, 17:05</span>
                            </div>
                            <div class="font-bold text-sm text-gray-200">Apple Laptop in Grey Leather Sleeve</div>
                            <div class="text-xs text-gray-400 mt-0.5">Handed to Library Front Helpdesk</div>
                        </div>
                    </div>

                    <!-- Mini Timeline -->
                    <div class="border-t border-[#2A3026] pt-3 text-xs font-mono text-gray-400 flex items-center justify-between">
                        <span>Private verification: Ready</span>
                        <span class="text-lime-400">Safe Return Pathway &rarr;</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section B: Live Discovery Preview -->
<section class="py-16 border-b border-[#2A3026]">
    <div class="trace-container">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
            <div>
                <span class="editorial-eyebrow">Real-Time Intelligence</span>
                <h2 class="text-3xl font-extrabold tracking-tight">Recent Community Reports</h2>
                <p class="text-gray-400 text-sm mt-1">Safe public signals currently active across the network</p>
            </div>
            <a href="/search" class="mt-4 md:mt-0 font-mono text-xs text-lime-400 hover:underline flex items-center gap-1">
                View All Public Reports (<?= $stats['total'] ?>) &rarr;
            </a>
        </div>

        <?php if (empty($recentReports)): ?>
            <div class="panel text-center py-12 text-gray-400">
                <p>No reports currently logged. Be the first to initiate a signal.</p>
                <a href="/reports/create/lost" class="btn btn-primary mt-4">+ Create First Report</a>
            </div>
        <?php else: ?>
            <div class="discovery-grid">
                <?php foreach ($recentReports as $report): ?>
                    <?php include dirname(__DIR__) . '/components/report_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Section C: How TRACE Works -->
<section id="how-it-works" class="py-16 border-b border-[#2A3026] bg-[#1B1E19]/40">
    <div class="trace-container">
        <div class="max-w-2xl mb-12">
            <span class="editorial-eyebrow">System Architecture</span>
            <h2 class="text-3xl font-extrabold tracking-tight">How TRACE Works</h2>
            <p class="text-gray-400 text-sm mt-2">A three-stage protocol designed for transparency, privacy, and verified recovery.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="panel border-t-2 border-t-lime-400">
                <div class="font-mono text-2xl font-bold text-lime-400 mb-2">01</div>
                <h3 class="font-bold text-lg mb-3">REPORT</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    Describe the item, where it was last seen or found, and general timestamps. For found items, private verification details are encrypted and kept secret.
                </p>
                <div class="font-mono text-xs text-gray-500 border-t border-[#2A3026] pt-3">
                    &bull; Safe public fields &bull; Secret custody notes
                </div>
            </div>

            <div class="panel border-t-2 border-t-lime-400">
                <div class="font-mono text-2xl font-bold text-lime-400 mb-2">02</div>
                <h3 class="font-bold text-lg mb-3">CONNECT</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    TRACE evaluates candidate reports through multi-signal correlation: category compatibility, token overlaps, colors, date decay, and Haversine spatial proximity.
                </p>
                <div class="font-mono text-xs text-gray-500 border-t border-[#2A3026] pt-3">
                    &bull; Deterministic scoring &bull; Plain-text rationale
                </div>
            </div>

            <div class="panel border-t-2 border-t-lime-400">
                <div class="font-mono text-2xl font-bold text-lime-400 mb-2">03</div>
                <h3 class="font-bold text-lg mb-3">RECOVER</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    Submit an ownership claim, answer the finder's secret verification questions, and coordinate a secure return at designated campus or community safe-desks.
                </p>
                <div class="font-mono text-xs text-gray-500 border-t border-[#2A3026] pt-3">
                    &bull; State machine verification &bull; Audit history
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section D: Recovery Impact (Live Database Metrics) -->
<section class="py-16 border-b border-[#2A3026]">
    <div class="trace-container">
        <div class="max-w-2xl mb-12">
            <span class="editorial-eyebrow">Empirical Verification</span>
            <h2 class="text-3xl font-extrabold tracking-tight">Recovery Network Impact</h2>
            <p class="text-gray-400 text-sm mt-1">Calculated directly from live database records without simulated metrics</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 font-mono">
            <div class="panel text-center py-8">
                <div class="text-4xl lg:text-5xl font-extrabold text-[#F4F2EA] mb-2"><?= $stats['total'] ?></div>
                <div class="text-xs text-gray-400 uppercase tracking-widest">Total Reports</div>
            </div>
            <div class="panel text-center py-8 border-lime-400/40">
                <div class="text-4xl lg:text-5xl font-extrabold text-lime-400 mb-2"><?= $stats['recovered'] ?></div>
                <div class="text-xs text-gray-400 uppercase tracking-widest">Items Recovered</div>
            </div>
            <div class="panel text-center py-8">
                <div class="text-4xl lg:text-5xl font-extrabold text-amber-400 mb-2"><?= $stats['active'] ?></div>
                <div class="text-xs text-gray-400 uppercase tracking-widest">Active Signals</div>
            </div>
            <div class="panel text-center py-8">
                <div class="text-4xl lg:text-5xl font-extrabold text-[#F4F2EA] mb-2"><?= $stats['recovery_rate'] ?>%</div>
                <div class="text-xs text-gray-400 uppercase tracking-widest">Recovery Success Rate</div>
            </div>
        </div>
    </div>
</section>

<!-- Section E: Closing CTA -->
<section class="py-20 bg-[#1B1E19]/70 text-center">
    <div class="trace-container max-w-3xl">
        <span class="editorial-eyebrow">Community Action</span>
        <h2 class="editorial-title text-3xl lg:text-4xl mb-6">
            "Your next clue could be someone's missing piece."
        </h2>
        <p class="editorial-lead mx-auto mb-8 text-base">
            Every minute an item sits unclaimed, the probability of recovery decays. Start a report today and join our privacy-first recovery circle.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/reports/create/lost" class="btn btn-primary btn-lg">
                Report a Lost Item
            </a>
            <a href="/reports/create/found" class="btn btn-outline btn-lg">
                Report a Found Item
            </a>
        </div>
    </div>
</section>
