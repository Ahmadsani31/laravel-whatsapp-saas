<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the WhatsApp engine connection and settings.
    |
    */

    'engine' => [
        'url' => env('WHATSAPP_ENGINE_URL', 'http://localhost:3000'),
        'host' => env('WHATSAPP_ENGINE_HOST', 'localhost'),
        'port' => env('WHATSAPP_ENGINE_PORT', 3000),
        'timeout' => env('WHATSAPP_ENGINE_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application URLs
    |--------------------------------------------------------------------------
    |
    | URLs used throughout the application for various purposes.
    |
    */

    'urls' => [
        'app' => env('APP_URL', 'http://localhost:8000'),
        'api' => env('APP_URL', 'http://localhost:8000') . '/api',
        'mcp' => env('APP_URL', 'http://localhost:8000') . '/api/mcp',
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Allowed origins for CORS requests.
    |
    */

    'cors' => [
        'origins' => [
            env('APP_URL', 'http://localhost:8000'),
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ],
    ],
];