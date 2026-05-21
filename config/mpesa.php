<?php

return [
    'api_key'               => env('MPESA_API_KEY'),
    'public_key'            => env('MPESA_PUBLIC_KEY'),
    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE', '000000'),
    'market'                => env('MPESA_MARKET', 'vodacomDRC'),
    'country'               => env('MPESA_COUNTRY', 'DRC'),
    'currency'              => env('MPESA_CURRENCY', 'USD'),
    'environment'           => env('MPESA_ENVIRONMENT', 'sandbox'),
    'callback_url'          => env('MPESA_CALLBACK_URL'),
    'admin_msisdn'          => env('MPESA_ADMIN_MSISDN'),
    // Set to true to skip real M-Pesa calls and simulate success (sandbox B2C is often unavailable)
    'simulate_b2c'          => env('MPESA_SIMULATE_B2C', false),
];
