<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
    'origin_id' => env('BITESHIP_ORIGIN_ID'),
    'webhook_secret' => env('BITESHIP_WEBHOOK_SECRET'),
];
