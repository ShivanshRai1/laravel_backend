<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://react-frontend-mauve-six.vercel.app',
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
    ],
    'allowed_origins_patterns' => [
        'https://*.vercel.app',
        'http://localhost:*',
        'http://127.0.0.1:*',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization', 'X-Requested-With', 'Content-Type', 'Accept', 'Origin'],
    'max_age' => 86400,
    'supports_credentials' => true,
];
