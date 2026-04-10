<?php

return [
    'driver' => env('PAYMENT_GATEWAY_DRIVER', 'qris_static'),

    'checkout_ttl_minutes' => (int) env('PAYMENT_GATEWAY_CHECKOUT_TTL_MINUTES', 30),

    'qris_static' => [
        'payload' => env('PAYMENT_GATEWAY_QRIS_STATIC_PAYLOAD'),
        'merchant_name' => env('PAYMENT_GATEWAY_QRIS_STATIC_MERCHANT_NAME'),
    ],
];
