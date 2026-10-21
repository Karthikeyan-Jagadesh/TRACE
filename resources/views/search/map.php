<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 pb-4 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Geographic Cluster Analysis</span>
            <h1 class="text-3xl font-extrabold tracking-tight">Spatial Map Intelligence</h1>
            <p class="text-gray-400 text-sm mt-1">Visualize lost and found incident density and active correlation clusters across locations</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <a href="/search" class="btn btn-outline btn-sm font-mono">&larr; Switch to Grid View</a>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap items-center gap-3 mb-6 font-mono text-xs">
        <span class="text-gray-400 uppercase text-[10px] tracking-wider">Quick Filter:</span>
        <a href="/map" class="px-2.5 py-1 border <?= empty($filters['type']) ? 'border-lime-400 bg-lime-400/10 text-lime-400' : 'border-[#2A3026] text-gray-400' ?>">All Reports</a>
        <a href="/map?type=lost" class="px-2.5 py-1 border <?= ($filters['type'] === 'lost') ? 'border-amber-400 bg-amber-400/10 text-amber-400' : 'border-[#2A3026] text-gray-400' ?>">Lost Only</a>
        <a href="/map?type=found" class="px-2.5 py-1 border <?= ($filters['type'] === 'found') ? 'border-lime-400 bg-lime-400/10 text-lime-400' : 'border-[#2A3026] text-gray-400' ?>">Found Only</a>
    </div>

    <!-- Interactive Leaflet Map Container -->
    <div class="panel p-0 overflow-hidden relative">
        <div id="trace-map" data-points="<?= htmlspecialchars(json_encode($points, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>" style="height: 650px;"></div>
        
        <!-- Legend Overlay -->
        <div class="absolute bottom-4 right-4 bg-[#111310]/90 border border-[#2A3026] backdrop-blur p-3 text-xs font-mono z-[1000] space-y-1.5">
            <div class="text-[10px] uppercase text-gray-400 tracking-wider mb-1">Signal Legend</div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <span>Lost Incident</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-lime-400"></span>
                <span>Found Item In Custody</span>
            </div>
        </div>
    </div>
</div>
