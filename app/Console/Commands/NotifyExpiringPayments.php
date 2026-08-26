<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Notifications\AppNotification;

class NotifyExpiringPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-expiring-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi untuk pesanan yang batas pembayarannya hampir habis.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa pesanan dengan batas pembayaran hampir habis...');

        // Cari order pending yang dibuat antara 18 jam hingga 24 jam yang lalu
        $limitTime = now()->subHours(18);
        $expireTime = now()->subHours(24);

        $orders = Order::where('payment_status', 'pending')
            ->where('created_at', '<=', $limitTime)
            ->where('created_at', '>', $expireTime)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $customer = $order->user;
            if ($customer) {
                // Cek apakah sudah pernah dikirimi notifikasi batas pembayaran hampir habis
                $alreadyNotified = $customer->notifications()
                    ->where('data->title', 'Batas Pembayaran Hampir Habis')
                    ->where('data->url', 'LIKE', '%' . $order->id . '%')
                    ->exists();

                if (!$alreadyNotified) {
                    $customer->notify(new AppNotification(
                        'Batas Pembayaran Hampir Habis',
                        "Pembayaran untuk pesanan #{$order->invoice_number} hampir kedaluwarsa. Silakan segera lakukan pembayaran.",
                        'clock',
                        'amber',
                        route('payment.pay', $order->id),
                        'pembayaran'
                    ));
                    $count++;
                }
            }
        }

        $this->info("Berhasil mengirim {$count} notifikasi batas pembayaran.");
    }
}
