<?php
use App\Helpers\Helper;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= Helper::escape($theme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Helper::csrfToken() ?>">
    <title><?= Helper::escape($pageTitle ?? 'TRACE — Lost & Found Intelligence Network') ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        obsidian: '#111310',
                        graphite: '#1B1E19',
                        lime: {
                            400: '#D7FF64',
                            500: '#C5EE52',
                        },
                        sage: '#929B88'
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Space Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Custom Spatial Editorial CSS -->
    <link rel="stylesheet" href="/assets/css/trace.css">
</head>
<body>

    <div class="app-wrapper">
        <!-- Global Header -->
        <?php include __DIR__ . '/header.php'; ?>

        <!-- Flash Messages -->
        <?php if (!empty($flashes)): ?>
            <div class="trace-container mt-4">
                <?php foreach ($flashes as $type => $message): ?>
                    <div class="alert alert-<?= ($type === 'success') ? 'success' : (($type === 'error') ? 'error' : 'info') ?> flex justify-between items-center">
                        <span><?= Helper::escape($message) ?></span>
                        <button type="button" data-dismiss="alert" class="text-sm font-bold opacity-75 hover:opacity-100 ml-4">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Main Page Content -->
        <main class="flex-1 py-6">
            <?= $content ?? '' ?>
        </main>

        <!-- Global Footer -->
        <?php include __DIR__ . '/footer.php'; ?>
    </div>

    <!-- Custom Client Script -->
    <script src="/assets/js/trace.js"></script>
</body>
</html>
