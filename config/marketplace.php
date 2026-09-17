<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Feature Flags
    |--------------------------------------------------------------------------
    | Fixed architecture decisions:
    | - Turkey first; global marketplace expansion disabled initially.
    | - Shopify adapter disabled initially; iyzico Marketplace is primary.
    */
    'enable_shopify' => (bool) env('ENABLE_SHOPIFY', false),
    'enable_global_marketplace' => (bool) env('ENABLE_GLOBAL_MARKETPLACE', false),

    'primary_currency' => 'TRY',
    'stock_reservation_minutes' => 20,

    'iyzico' => [
        'mode' => env('IYZICO_MODE', 'sandbox'),
        'api_key' => env('IYZICO_API_KEY', ''),
        'secret_key' => env('IYZICO_SECRET_KEY', ''),
        'base_url' => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com'),
        'webhook_secret' => env('IYZICO_WEBHOOK_SECRET', ''),
    ],
];
