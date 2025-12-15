<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CPD Payment Gateway configuration
    |--------------------------------------------------------------------------
    |
    | "gateway" can be:
    |   - "mock"  : use internal mock (redirects to pay_mock route)
    |   - "http"  : use an HTTP API / hosted checkout URL (real gateway)
    |
    */

    'gateway' => env('CPD_PAYMENT_GATEWAY', 'mock'),

    // For real gateway mode ("http")
    'init_url' => env('CPD_PAYMENT_INIT_URL'),   // e.g. https://api.yourgateway.com/checkout
    'secret'   => env('CPD_PAYMENT_SECRET'),     // API key / secret
    'public'   => env('CPD_PAYMENT_PUBLIC'),     // optional public key
];
