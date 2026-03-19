<?php

return [
    'mode'             => env('GN_MODE', 'sandbox'), // 'sandbox' ou 'production'
    'is_sandbox'       => env('GN_MODE', 'sandbox') !== 'production',
    'debug'            => env('GN_DEBUG', false),
    'default_key_pix'  => env('GN_DEFAULT_KEY_PIX'),
    'contrato_pix'     => env('GN_CONTRATO_PIX'),
    'plan_id'          => (int) env('GN_PLAN_ID', 13289),
    'notification_url' => env('GN_NOTIFICATION_URL', 'https://cotafacil.bmsys.com.br/callback'),
    'cdn_account_id'   => env('GN_CDN_ACCOUNT_ID', '3c8b1529ac6f42b8d524cce476dca9ab'),
    'sandbox' => [
        'client_id'        => env('GN_TEST_CLIENT_ID'),
        'client_secret'    => env('GN_TEST_CLIENT_SECRET'),
        'certificate_name' => env('GN_TEST_CERTIFICATE_NAME'),
    ],
    'production' => [
        'client_id'        => env('GN_PROD_CLIENT_ID'),
        'client_secret'    => env('GN_PROD_CLIENT_SECRET'),
        'certificate_name' => env('GN_PROD_CERTIFICATE_NAME'),
    ],
];
