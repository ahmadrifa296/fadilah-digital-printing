<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Models\ActivityLog;
use App\Models\StockLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderWorkflowService
{
   /**
    * Mark order as paid.
    * Transitions from PENDING -> DIPROSES (cetak) or SIAP_DIKEMAS (non-cetak).
    */
   public function markPaid(Order $order): void
   {
       DB::transaction(function () use ($order) {
           // Validate transition: Must be pending to become paid/processed
           if ($order->order_status !== OrderStatus::PENDING) {
               Log::warning("OrderWorkflowService: markPaid rejected for order #{$order->invoice_number}. Current status: {$order->order_status->value}");
               return;
           }

           $order->loadMissing('orderDetails.product');
           $hasCetak = $order->orderDetails->contains(function ($detail) {
               return $detail->product?->requires_design_file === true;
           });

           $targetStatus = $hasCetak ? OrderStatus::DIPROSES : OrderStatus::SIAP_DIKEMAS;

           $order->update([
               'payment_status' => 'settlement',
               'order_status' => $targetStatus,
               'status' => 'paid', // legacy
               'paid_at' => Carbon::now(),
           ]);

           ActivityLog::create([
               'user_id' => Auth::id() ?: $order->user_id,
               'activity' => 'Pembayaran Sukses',
               'description' => "Pembayaran order #{$order->invoice_number} lunas. Status otomatis menjadi: " . $targetStatus->label(),
               'ip_address' => request()->ip() ?: '127.0.0.1',
               'user_agent' => request()->userAgent() ?: 'System',
           ]);
       });
    }

    /**
     * Start printing.
     * Transitions from DIPROSES -> SEDANG_DICETAK.
     */
    public function startPrinting(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            if ($order->order_status !== OrderStatus::DIPROSES) {
                return false;
            }

            $order->loadMissing('orderDetails.product');
            $hasCetak = $order->orderDetails->contains(function ($detail) {
                return $detail->product?->requires_design_file === true;
            });

            if ($hasCetak) {
                $order->update([
                    'order_status' => OrderStatus::SEDANG_DICETAK,
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Proses Cetak Otomatis',
                    'description' => "Order #{$order->invoice_number} berpindah ke Sedang Dicetak karena admin membuka/mengunduh file desain.",
                    'ip_address' => request()->ip() ?: '127.0.0.1',
                    'user_agent' => request()->userAgent() ?: 'System',
                ]);

                return true;
            }

            return false;
        });
    }

    /**
     * Finish printing.
     * Transitions from SEDANG_DICETAK -> SIAP_DIKEMAS.
     */
    public function finishPrinting(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            if ($order->order_status !== OrderStatus::SEDANG_DICETAK) {
                return false;
            }

           $order->update([
               'order_status' => OrderStatus::SIAP_DIKEMAS,
           ]);

           ActivityLog::create([
               'user_id' => Auth::id(),
               'activity' => 'Produksi Selesai',
               'description' => "Produksi order #{$order->invoice_number} selesai, status siap dikemas.",
               'ip_address' => request()->ip() ?: '127.0.0.1',
               'user_agent' => request()->userAgent() ?: 'System',
           ]);

           return true;
       });
   }

   /**
    * Pack order (Buat Resi).
    * Transitions from SIAP_DIKEMAS -> DIKEMAS.
    */
   public function pack(Order $order, string $trackingNumber, ?string $shipmentId, ?string $status): void
   {
       DB::transaction(function () use ($order, $trackingNumber, $shipmentId, $status) {
           if ($order->order_status !== OrderStatus::SIAP_DIKEMAS) {
               throw new \Exception("Pesanan #{$order->invoice_number} tidak berada dalam status Siap Dikemas.");
           }

           $order->update([
               'tracking_number' => $trackingNumber,
               'biteship_order_id' => $shipmentId,
               'shipping_status' => $status ?: 'allocated',
               'order_status' => OrderStatus::DIKEMAS,
           ]);

           ActivityLog::create([
               'user_id' => Auth::id() ?: $order->user_id,
               'activity' => 'Buat Resi',
               'description' => "Nomor resi {$trackingNumber} telah dibuat untuk order #{$order->invoice_number}.",
               'ip_address' => request()->ip() ?: '127.0.0.1',
               'user_agent' => request()->userAgent() ?: 'System',
           ]);
        });
    }

    /**
     * Ship order (Kirim Pesanan).
     * Transitions from DIKEMAS -> DIKIRIM.
     */
     public function ship(Order $order): void
     {
         DB::transaction(function () use ($order) {
             if (!in_array($order->order_status, [OrderStatus::DIKEMAS, OrderStatus::SIAP_DIKEMAS])) {
                 throw new \Exception("Pesanan #{$order->invoice_number} tidak dalam status Dikemas atau Siap Dikemas.");
             }

             // Generate tracking number if empty or not matching FDP format
             $resi = $order->tracking_number;
             if (empty($resi) || !str_starts_with($resi, 'FDP-')) {
                 $resi = 'FDP-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
             }

             $order->update([
                 'order_status' => OrderStatus::DIKIRIM,
                 'tracking_number' => $resi,
             ]);

             // Update or create shipment
             $shipment = $order->shipment;
             if (!$shipment) {
                 $shipment = \App\Models\Shipment::create([
                     'order_id' => $order->id,
                     'tracking_number' => $resi,
                     'courier' => $order->shipping_courier ?: 'jne',
                     'service' => $order->shipping_service ?: 'reg',
                     'status' => 'In Transit',
                     'pickup_status' => 'picked_up',
                 ]);
             } else {
                 $shipment->update([
                     'tracking_number' => $resi,
                     'status' => 'In Transit',
                     'pickup_status' => 'picked_up'
                 ]);
             }

             $trackingService = app(\App\Services\TrackingService::class);
             $trackingService->updateStatus(
                 $shipment,
                 'In Transit',
                 'Pesanan telah dikirim.',
                 null
             );

             ActivityLog::create([
                 'user_id' => Auth::id() ?: $order->user_id,
                 'activity' => 'Kirim Paket',
                 'description' => "Paket untuk order #{$order->invoice_number} telah dikirim dengan nomor resi {$resi}.",
                 'ip_address' => request()->ip() ?: '127.0.0.1',
                 'user_agent' => request()->userAgent() ?: 'System',
             ]);
         });
     }

    /**
     * Complete order (Pesanan Diterima).
     * Transitions from DIKIRIM -> SELESAI.
     */
    public function complete(Order $order, string $actorType = 'Customer'): void
    {
        DB::transaction(function () use ($order, $actorType) {
            // Selesaikan pesanan can be done from DIKIRIM (standard workflow)
            // or from packaging if admin forces completion.
            $order->update([
                'order_status' => OrderStatus::SELESAI,
                'status' => 'paid', // legacy
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'status' => 'Delivered'
                ]);

                $trackingService = app(\App\Services\TrackingService::class);
                $trackingService->updateStatus(
                    $order->shipment,
                    'Delivered',
                    'Pesanan diterima pelanggan.',
                    null
                );
            }

            ActivityLog::create([
                'user_id' => Auth::id() ?: $order->user_id,
                'activity' => 'Pesanan Selesai',
                'description' => "{$actorType} menandai pesanan #{$order->invoice_number} telah selesai.",
                'ip_address' => request()->ip() ?: '127.0.0.1',
                'user_agent' => request()->userAgent() ?: 'System',
            ]);
        });
    }

    /**
     * Cancel order and restore stock.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => OrderStatus::DIBATALKAN,
                'status' => 'cancelled', // legacy
            ]);

            // Restore stock
            $order->loadMissing('orderDetails.product');
            foreach ($order->orderDetails as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->qty);

                    StockLog::create([
                        'product_id' => $detail->product_id,
                        'type' => 'in',
                        'quantity' => $detail->qty,
                        'description' => "Pengembalian stok otomatis akibat pembatalan pesanan #{$order->invoice_number}",
                        'user_id' => Auth::id() ?: $order->user_id,
                    ]);
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id() ?: $order->user_id,
                'activity' => 'Pembatalan Pesanan',
                'description' => "Pesanan #{$order->invoice_number} telah dibatalkan.",
                'ip_address' => request()->ip() ?: '127.0.0.1',
                'user_agent' => request()->userAgent() ?: 'System',
            ]);
        });
    }
}
