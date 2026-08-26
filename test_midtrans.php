<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class
]);
echo "MIDTRANS SERVER KEY: " . config('midtrans.server_key') . "\n";
echo "MIDTRANS CLIENT KEY: " . config('midtrans.client_key') . "\n";
echo "MIDTRANS IS PRODUCTION: " . (config('midtrans.is_production') ? 'true' : 'false') . "\n";
echo "MIDTRANS SNAP URL: " . config('midtrans.snap_url') . "\n";
