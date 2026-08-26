<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan notifikasi batas pembayaran hampir habis setiap jam
Schedule::command('app:notify-expiring-payments')->hourly();

// Jadwalkan auto-complete order yang berstatus Delivered selama 3 hari
Artisan::command('orders:autocomplete-delivered', function () {
    \App\Models\Order::autoCompleteDeliveredOrders();
    $this->info('Delivered orders older than 3 days have been automatically completed.');
})->purpose('Auto-complete delivered orders older than 3 days');

Schedule::command('orders:autocomplete-delivered')->daily();
