<?php
return [
    'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),

    'fonnte' => [
        'api_key' => env('FONNTE_API_KEY'),
    ],

    'wablas' => [
        'domain' => env('WABLAS_DOMAIN', 'https://solo.wablas.com'),
        'token' => env('WABLAS_TOKEN'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],
];
