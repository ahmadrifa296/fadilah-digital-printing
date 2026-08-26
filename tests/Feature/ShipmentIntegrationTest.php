<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Shipment;
use App\Services\BiteshipService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use App\Services\LabelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Order $order;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $category = Category::create([
            'category_name' => 'Spanduk',
            'slug' => 'spanduk',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Banner Flexy',
            'product_type' => 'custom',
            'price' => 50000,
            'stock' => 10,
            'weight' => 500,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'requires_design_file' => false,
        ]);

        $this->order = Order::create([
            'user_id' => $this->customer->id,
            'invoice_number' => 'FDP-20260712-0001',
            'order_status' => 'pending',
            'payment_status' => 'settlement', // Paid status
            'total_price' => 65000,
            'status' => 'pending',
            'receiver_name' => 'Receiver Doe',
            'phone' => '08123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Barat',
            'district' => 'Palmerah',
            'subdistrict' => 'Kemanggisan',
            'postal_code' => '11480',
            'full_address' => 'Jl. Kemanggisan Raya No. 10',
            'shipping_cost' => 15000,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'shipping_estimation' => '2-3 Hari',
            'shipping_status' => 'pending',
        ]);

        OrderDetail::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'subtotal' => 50000,
        ]);
    }

    /**
     * Test shipment creation service.
     */
    public function test_shipment_creation_service()
    {
        // Mock BiteshipService to return success but no waybill (simulating sandbox)
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('createShipment')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => [
                        'id' => 'biteship_mock_order_123',
                        'status' => 'allocated',
                    ]
                ]);
        });

        $shipmentService = app(ShipmentService::class);
        $shipment = $shipmentService->createShipmentForOrder($this->order);

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertEquals('biteship_mock_order_123', $shipment->shipment_id);
        $this->assertNotEmpty($shipment->tracking_number); // Check dummy fallback
        $this->assertStringStartsWith('FDP-' . date('Ymd'), $shipment->tracking_number);
        $this->assertEquals('Order Created', $shipment->status);

        // Check if order columns are synchronized
        $this->order->refresh();
        $this->assertEquals($shipment->tracking_number, $this->order->tracking_number);
        $this->assertEquals('allocated', $this->order->shipping_status);

        // Check if initial tracking history was created
        $this->assertCount(1, $shipment->trackings);
        $this->assertEquals('Order Created', $shipment->trackings->first()->status);
    }

    /**
     * Test tracking status updates.
     */
    public function test_tracking_status_updates()
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipment_id' => 'biteship_123',
            'courier' => 'jne',
            'service' => 'reg',
            'tracking_number' => 'FDP240000001',
            'status' => 'allocated',
        ]);

        $trackingService = app(TrackingService::class);
        $trackingService->updateStatus($shipment, 'In Transit', 'Paket sedang dalam perjalanan.', 'Bandung Hub');

        $shipment->refresh();
        $this->assertEquals('In Transit', $shipment->status);
        $this->assertCount(1, $shipment->trackings);
        
        $lastTracking = $shipment->trackings->first();
        $this->assertEquals('In Transit', $lastTracking->status);
        $this->assertEquals('Paket sedang dalam perjalanan.', $lastTracking->description);
        $this->assertEquals('Bandung Hub', $lastTracking->location);

        $this->order->refresh();
        $this->assertEquals('in_transit', $this->order->shipping_status);
    }

    /**
     * Test label service generates pdf output.
     */
    public function test_label_service_pdf_generation()
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipment_id' => 'biteship_123',
            'courier' => 'jne',
            'service' => 'reg',
            'tracking_number' => 'FDP240000001',
            'status' => 'allocated',
        ]);

        $labelService = app(LabelService::class);
        $pdfOutput = $labelService->generateLabelPdf($shipment);

        $this->assertNotEmpty($pdfOutput);
        $this->assertStringStartsWith('%PDF', $pdfOutput);
    }

    /**
     * Test admin routes for shipping simulation.
     */
    public function test_admin_shipping_simulation_endpoints()
    {
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('createShipment')->andReturn([
                'success' => true,
                'data' => [
                    'id' => 'biteship_123',
                    'status' => 'allocated',
                ]
            ]);
        });

        // Test Buat Resi
        $this->order->order_status = \App\Enums\OrderStatus::SIAP_DIKEMAS;
        $this->order->save();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.shipping.shipments.create', $this->order->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shipment = Shipment::where('order_id', $this->order->id)->first();
        $this->assertNotNull($shipment);

        // Test Simulate Pickup
        $responsePickup = $this->actingAs($this->admin)
            ->post(route('admin.shipping.shipments.pickup', $shipment->id), [
                'pickup_status' => 'picked_up',
            ]);
        $responsePickup->assertRedirect();
        $shipment->refresh();
        $this->assertEquals('picked_up', $shipment->pickup_status);

        // Test Update Tracking
        $responseTracking = $this->actingAs($this->admin)
            ->post(route('admin.shipping.shipments.tracking', $shipment->id), [
                'status' => 'In Transit',
                'description' => 'Paket telah dikirim.',
                'location' => 'Jakarta Hub',
            ]);
        $responseTracking->assertRedirect();

        // Test Cetak Label
        $responseLabel = $this->actingAs($this->admin)
            ->get(route('admin.shipping.shipments.label', $shipment->id));
        $responseLabel->assertOk();
        $responseLabel->assertHeader('Content-Type', 'application/pdf');
    }
}
