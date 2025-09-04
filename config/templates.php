<?php

return [
    // e.g. "/srv/templates" in production.
    'root' => env('TEMPLATES_ROOT', base_path('storage/fixtures/templates')),

    // How to build the public demo link:
    'demo_url_pattern' => env('DEMO_URL_PATTERN', 'https://wordpress.metanow.dev/{slug}/'),

    // Relative paths to look for a screenshot (first one that exists wins)
    'screenshot_candidates' => [
        'wp-content/themes/*/screenshot.png',
        'wp-content/themes/*/screenshot.jpg',
        'screenshot.png',
    ],
];
