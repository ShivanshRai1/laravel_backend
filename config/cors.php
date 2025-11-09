<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://localhost:3002',
        'http://localhost:3003',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://127.0.0.1:3002',
        'http://127.0.0.1:3003',
        'https://dashboard-react-main.netlify.app',
        env('FRONTEND_URL', 'https://dashboard-react-main.netlify.app')
    ],
    // Allow any case for localhost using a regex pattern
    'allowed_origins_patterns' => [
        '/^http:\/\/(localhost|LOCALHOST|[lL][oO][cC][aA][lL][hH][oO][sS][tT]):3001$/',
        '/^http:\/\/(localhost|LOCALHOST|[lL][oO][cC][aA][lL][hH][oO][sS][tT]):3000$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
