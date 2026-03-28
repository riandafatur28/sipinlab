<?php
// config/whatsapp.php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider
    |--------------------------------------------------------------------------
    |
    | Supported: 'wablas', 'fonnte', 'twilio', 'meta'
    |
    */
    'provider' => env('WA_PROVIDER', 'wablas'),

    /*
    |--------------------------------------------------------------------------
    | Wablas Configuration
    |--------------------------------------------------------------------------
    */
    'wablas' => [
        'domain' => env('WABLAS_DOMAIN', 'https://app.wablas.com'),
        'token' => env('WABLAS_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonnte Configuration (alternative)
    |--------------------------------------------------------------------------
    */
    'fonnte' => [
        'domain' => env('FONNTE_DOMAIN', 'https://api.fonnte.com'),
        'token' => env('FONNTE_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback: Disable WhatsApp if not configured
    |--------------------------------------------------------------------------
    */
    'enabled' => env('WA_ENABLED', true),
];
