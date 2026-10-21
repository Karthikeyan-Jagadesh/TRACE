<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'TRACE',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'secret' => $_ENV['APP_SECRET'] ?? 'trc_secret_key_8f3a9d21c4e7b6a59281039485720193',
    'expiration' => [
        'lost_days' => (int)($_ENV['REPORT_EXPIRATION_DAYS_LOST'] ?? 60),
        'found_days' => (int)($_ENV['REPORT_EXPIRATION_DAYS_FOUND'] ?? 90),
    ],
    'matching' => [
        'min_score' => (int)($_ENV['MATCH_MIN_SCORE'] ?? 40),
        'weights' => [
            'category' => 20,
            'item_title' => 20,
            'description' => 15,
            'brand' => 10,
            'color' => 10,
            'date_proximity' => 10,
            'geo_proximity' => 15,
        ]
    ]
];
