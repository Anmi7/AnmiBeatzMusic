<?php

$defaultAllowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
];

$envAllowedOrigins = array_filter(
    array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))
);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin requests are allowed on
    | your API. The "allowed_methods" and "allowed_headers" may be adjusted
    | as needed to accommodate the needs of your application.
    |
    | You may even set the CORS options to return true, which will result in
    | the server sending a general CORS header for every request to any of
    | your routes, making the API publically accessible to any domain.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => !empty($envAllowedOrigins) ? $envAllowedOrigins : $defaultAllowedOrigins,

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
