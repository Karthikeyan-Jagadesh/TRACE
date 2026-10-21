<?php
use App\Helpers\Helper;
?>
<div class="trace-container py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-6 border-b border-[#2A3026]">
        <div>
            <span class="editorial-eyebrow">Public Signal Discovery</span>
            <h1 class="text-3xl font-extrabold tracking-tight">Discover Network Reports</h1>
            <p class="text-gray-400 text-sm mt-1">
                Showing <?= count($reports) ?> of <?= $total ?> active reports across campuses and shared spaces
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="/map" class="btn btn-outline btn-sm font-mono flex items-center gap-1.5">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Switch to Spatial Map
            </a>
            <a href="/reports/create/lost" class="btn btn-primary btn-sm">+ Report Lost</a>
            <a href="/reports/create/found" class="btn btn-outline btn-sm">+ Report Found</a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <form action="/search" method="GET" class="panel mb-8 p-4 bg-[#1B1E19]">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
            <!-- Keyword Search -->
            <div class="lg:col-span-2">
                <label class="form-label text-xs" for="q">Keyword Search</label>
                <input type="text" 
                       id="q" 
                       name="q" 
                       value="<?= Helper::escape($filters['q']) ?>" 
                       placeholder="Item name, description, brand, or location..." 
                       class="form-control text-sm">
            </div>

            <!-- Report Type -->
            <div>
                <label class="form-label text-xs" for="type">Signal Type</label>
                <select id="type" name="type" class="form-control text-sm">
                    <option value="">All (Lost &amp; Found)</option>
                    <option value="lost" <?= ($filters['type'] === 'lost') ? 'selected' : '' ?>>Lost Only</option>
                    <option value="found" <?= ($filters['type'] === 'found') ? 'selected' : '' ?>>Found Only</option>
                </select>
            </div>

            <!-- Category -->
            <div>
                <label class="form-label text-xs" for="category">Category</label>
                <select id="category" name="category" class="form-control text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($filters['category'] === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Brand -->
            <div>
                <label class="form-label text-xs" for="brand">Brand</label>
                <input type="text" 
                       id="brand" 
                       name="brand" 
                       value="<?= Helper::escape($filters['brand']) ?>" 
                       placeholder="Apple, Sony, etc." 
                       class="form-control text-sm">
            </div>

            <!-- Location -->
            <div>
                <label class="form-label text-xs" for="location">Location</label>
                <input type="text" 
                       id="location" 
                       name="location" 
                       value="<?= Helper::escape($filters['location']) ?>" 
                       placeholder="Library, Cafeteria, etc." 
                       class="form-control text-sm">
            </div>

            <!-- Date From -->
            <div>
                <label class="form-label text-xs" for="date_from">Date From</label>
                <input type="date" 
                       id="date_from" 
                       name="date_from" 
                       value="<?= Helper::escape($filters['date_from']) ?>" 
                       class="form-control text-sm font-mono">
            </div>

            <!-- Sort By -->
            <div>
                <label class="form-label text-xs" for="sort">Sort By</label>
                <select id="sort" name="sort" class="form-control text-sm">
                    <option value="recent" <?= ($filters['sort'] === 'recent') ? 'selected' : '' ?>>Most Recent</option>
                    <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title" <?= ($filters['sort'] === 'title') ? 'selected' : '' ?>>Item Name A-Z</option>
                </select>
            </div>
        </div>

        <div class="flex justify-between items-center pt-3 border-t border-[#2A3026]">
            <a href="/search" class="text-xs text-gray-400 hover:text-lime-400">Clear All Filters</a>
            <button type="submit" class="btn btn-primary btn-sm px-6">Apply Filters</button>
        </div>
    </form>

    <!-- Results Grid -->
    <?php if (empty($reports)): ?>
        <div class="panel text-center py-16 text-gray-400">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-bold text-gray-200 mb-1">No Matching Reports Found</h3>
            <p class="text-xs max-w-sm mx-auto mb-4">Try broadening your search terms, clearing specific category filters, or expanding the date range.</p>
            <a href="/search" class="btn btn-outline btn-sm">Reset Search</a>
        </div>
    <?php else: ?>
        <div class="discovery-grid mb-12">
            <?php foreach ($reports as $report): ?>
                <?php include dirname(__DIR__) . '/components/report_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Server-Side Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center gap-2 font-mono text-xs">
                <?php if ($currentPage > 1): ?>
                    <a href="/search?<?= http_build_query(array_merge($filters, ['page' => $currentPage - 1])) ?>" class="btn btn-outline btn-sm">&larr; Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="/search?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>" 
                       class="px-3 py-1.5 border <?= ($p === $currentPage) ? 'border-lime-400 bg-lime-400 text-black font-bold' : 'border-[#2A3026] text-gray-300 hover:border-gray-400' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="/search?<?= http_build_query(array_merge($filters, ['page' => $currentPage + 1])) ?>" class="btn btn-outline btn-sm">Next &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
