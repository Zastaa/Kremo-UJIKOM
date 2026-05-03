<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-your_server_key_here'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-your_client_key_here'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];
