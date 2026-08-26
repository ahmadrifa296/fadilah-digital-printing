<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentTracking;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    /**
     * Update shipment status and create a new tracking record.
     */
    public function updateStatus(Shipment $shipment, string $status, string $description, ?string $location = null): ShipmentTracking
    {
        Log::info("TrackingService: Updating shipment #{$shipment->id} status to '{$status}'", [
            'description' => $description,
            'location' => $location
        ]);

        // Create tracking record in DB
        $tracking = ShipmentTracking::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'description' => $description,
            'location' => $location,
        ]);

        // Determine if we need to sync status to shipment/order
        $shipment->update([
            'status' => $status
        ]);

        // Sync to order
        $order = $shipment->order;
        if ($order) {
            $shippingStatusMap = [
                'Order Created' => 'allocated',
                'Waiting Pickup' => 'waiting_pickup',
                'Pickup Requested' => 'pickup_requested',
                'Picked Up' => 'picked_up',
                'Sorting Facility' => 'sorting',
                'In Transit' => 'in_transit',
                'Out For Delivery' => 'out_for_delivery',
                'Delivered' => 'delivered',
            ];

            $orderUpdate = [
                'shipping_status' => $shippingStatusMap[$status] ?? strtolower($status),
            ];

            // If delivered, mark global order status as selesai/delivered
            if (strtolower($status) === 'delivered') {
                $orderUpdate['order_status'] = 'selesai';
                $orderUpdate['status'] = 'paid'; // legacy compatibility
            }

            $order->update($orderUpdate);

            // Send notification to customer
            if ($order->user) {
                try {
                    $order->user->notify(new \App\Notifications\AppNotification(
                        'Update Pengiriman',
                        "Status pengiriman untuk pesanan #{$order->invoice_number}: {$status} - {$description}.",
                        'truck',
                        'blue',
                        route('dashboard') . '?tab=pesanan',
                        'pesanan'
                    ));
                } catch (\Exception $e) {
                    Log::error('TrackingService: Failed to notify user: ' . $e->getMessage());
                }
            }
        }

        return $tracking;
    }
}
