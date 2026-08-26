<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShipmentService
{
   protected BiteshipService $biteshipService;
   protected TrackingService $trackingService;

   public function __construct(BiteshipService $biteshipService, TrackingService $trackingService)
   {
       $this->biteshipService = $biteshipService;
       $this->trackingService = $trackingService;
   }

   /**
    * Create shipment for a paid order.
    */
   public function createShipmentForOrder(Order $order): Shipment
   {
       // 1. Ensure order is paid
       if (!$order->isPaid()) {
           throw new \Exception("Pesanan #{$order->invoice_number} belum lunas dibayar.");
       }

       // Load relations if not loaded
       $order->loadMissing(['orderDetails.product', 'user']);

       // 2. Check if shipment already exists
       if ($order->shipment) {
           return $order->shipment;
       }

       // 3. Resolve origin and destination details
       $shipperName = Setting::getVal('warehouse_name') ?: Setting::getVal('web_name') ?: 'Fadilah Digital Printing';
       $shipperPhone = Setting::getVal('web_phone') ?: '081234567890';
       $shipperEmail = Setting::getVal('web_email') ?: 'fadilahprinting@gmail.com';
       $shipperAddress = Setting::getVal('warehouse_address') ?: Setting::getVal('web_address') ?: 'Sleman, D.I. Yogyakarta';
       $shipperPostalCode = (int) (Setting::getVal('warehouse_postal_code') ?: 55281);
       
       // Find origin area ID
       $originAreaId = Setting::getVal('biteship_origin_id');
       if (empty($originAreaId)) {
           $originAreaId = $this->resolveAreaIdByPostalCode($shipperPostalCode);
       }

       // Find destination area ID
       $destinationPostalCode = (int) $order->postal_code;
       $destinationAreaId = $this->resolveAreaIdByPostalCode($destinationPostalCode);

       // 4. Map items
       $items = [];
       foreach ($order->orderDetails as $detail) {
           $product = $detail->product;
           $weight = ($product && $product->weight > 0) ? $product->weight : 100;
           $isSizeBased = $product ? in_array($product->calculation_type, ['custom_size', 'quantity_custom_size']) : false;
           
           if ($isSizeBased && isset($detail->custom_length) && isset($detail->custom_width)) {
               $area = $detail->custom_length * $detail->custom_width;
               $weight = (int) round($weight * $area);
           }

           $items[] = [
               'name' => $product ? $product->product_name : 'Produk Cetak',
               'description' => substr(($product ? $product->description : 'Cetak Custom') ?? '', 0, 50),
               'value' => (int) ($detail->subtotal / $detail->qty),
               'weight' => $weight,
               'quantity' => (int) $detail->qty,
               'length' => (int) (($product && $product->length > 0) ? $product->length : 10),
               'width' => (int) (($product && $product->width > 0) ? $product->width : 10),
               'height' => (int) (($product && $product->height > 0) ? $product->height : 5)
           ];
       }

       // 5. Build Biteship request payload
       $courierCompany = strtolower($order->shipping_courier ?: 'jne');
       $courierType = strtolower($order->shipping_service ?: 'reg');

       $payload = [
           'shipper_contact_name' => $shipperName,
           'shipper_contact_phone' => $shipperPhone,
           'shipper_contact_email' => $shipperEmail,
           'origin_contact_name' => $shipperName,
           'origin_contact_phone' => $shipperPhone,
           'origin_address' => $shipperAddress,
           'origin_postal_code' => $shipperPostalCode,
           'destination_contact_name' => $order->receiver_name,
           'destination_contact_phone' => $order->phone,
           'destination_contact_email' => $order->user->email ?? $shipperEmail,
           'destination_address' => $order->full_address,
           'destination_postal_code' => $destinationPostalCode,
           'courier_company' => $courierCompany,
           'courier_type' => $courierType,
           'delivery_type' => 'now',
           'items' => $items,
       ];

       if (!empty($originAreaId)) {
           $payload['origin_area_id'] = $originAreaId;
       }
       if (!empty($destinationAreaId)) {
           $payload['destination_area_id'] = $destinationAreaId;
       }

       // Log request payload to laravel.log
       Log::info('ShipmentService: Biteship Create Shipment Request payload:', $payload);

       // 6. Call Biteship API
       $response = $this->biteshipService->createShipment($payload);
       
       // Log response to laravel.log
       Log::info('ShipmentService: Biteship Create Shipment Response:', $response);

       $shipmentId = null;
       $trackingNumber = null;
       $labelUrl = null;
       $status = 'allocated';
       $rawResponse = $response;

       if ($response['success'] && isset($response['data']['id'])) {
           $data = $response['data'];
           $shipmentId = $data['id'];
           $status = $data['status'] ?? 'allocated';
           
           if (isset($data['courier']['waybill_id']) && !empty($data['courier']['waybill_id'])) {
               $trackingNumber = $data['courier']['waybill_id'];
           }
           if (isset($data['label_url']) && !empty($data['label_url'])) {
               $labelUrl = $data['label_url'];
           }
       }

       // 7. Fallback to dummy if no tracking/resi was given (sandbox limitation)
       if (empty($trackingNumber)) {
           $trackingNumber = 'FDP-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
           Log::info("ShipmentService: Biteship did not return resi. Falling back to dummy: {$trackingNumber}");
       }

       // 8. Create Shipment record in DB
       $shipment = Shipment::create([
           'order_id' => $order->id,
           'shipment_id' => $shipmentId,
           'courier' => $order->shipping_courier ?: 'jne',
           'service' => $order->shipping_service ?: 'reg',
           'tracking_number' => $trackingNumber,
           'status' => $status,
           'estimated_days' => $order->shipping_estimation,
           'label_url' => $labelUrl,
           'pickup_status' => 'waiting_pickup',
           'raw_response' => $rawResponse,
       ]);

       // Sync shipment columns to order table
       $order->update([
           'tracking_number' => $trackingNumber,
           'biteship_order_id' => $shipmentId,
           'shipping_status' => $status ?: 'allocated',
       ]);

       // 9. Automatically seed initial tracking history
       $this->trackingService->updateStatus(
           $shipment,
           'Order Created',
           "Pesanan telah berhasil dikonfirmasi dan nomor resi pengiriman {$trackingNumber} telah diterbitkan."
       );

       return $shipment;
   }

   /**
    * Helper: Resolve area ID by postal code using Biteship Maps API
    */
   protected function resolveAreaIdByPostalCode(int $postalCode): ?string
   {
       $apiKey = Setting::getVal('biteship_api_key') ?: config('biteship.api_key');
       $baseUrl = Setting::getVal('biteship_base_url') ?: config('biteship.base_url', 'https://api.biteship.com');

       if (empty($apiKey)) {
           return null;
       }

       try {
           $response = Http::withHeaders([
               'Authorization' => $apiKey,
               'Accept' => 'application/json'
           ])->get("{$baseUrl}/v1/maps/areas", [
               'query' => $postalCode
           ]);

           if ($response->successful()) {
               $areas = $response->json()['areas'] ?? [];
               if (count($areas) > 0) {
                   return $areas[0]['id'];
               }
           }
       } catch (\Exception $e) {
           Log::error('ShipmentService: Failed to resolve area ID for postal code ' . $postalCode . ': ' . $e->getMessage());
       }

       return null;
   }
}
