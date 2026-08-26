<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest cannot access admin shipping dashboard
     */
    public function test_guest_cannot_access_admin_shipping()
    {
        $response = $this->get(route('admin.shipping.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Customer cannot access admin shipping dashboard (redirected to '/')
     */
    public function test_customer_cannot_access_admin_shipping()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $response = $this->actingAs($customer)->get(route('admin.shipping.index'));
        $response->assertRedirect('/');
    }

    /**
     * Admin can access admin shipping dashboard
     */
    public function test_admin_can_access_admin_shipping()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.shipping.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengiriman');
    }

    /**
     * Admin can update waybill tracking number (resi)
     */
    public function test_admin_can_update_resi()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-TEST-123',
            'order_status' => 'diproses',
            'payment_status' => 'settlement',
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.shipping.update_resi', $order->id), [
            'tracking_number' => 'RESI-TEST-999',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'tracking_number' => 'RESI-TEST-999',
            'shipping_status' => 'dikirim',
            'order_status' => 'dikirim',
        ]);
    }

    /**
     * Customer can calculate rates for checkout
     */
    public function test_customer_can_calculate_rates()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        
        $address = UserAddress::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '02',
            'rw' => '15',
            'no_rumah' => '12B',
            'full_address' => 'Jl. Dago No. 12',
            'is_default' => true
        ]);

        $category = Category::create(['category_name' => 'Umum']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Kartu Nama',
            'price' => 100000,
            'stock' => 10,
            'product_type' => 'custom',
            'weight' => 500,
            'length' => 10,
            'width' => 10,
            'height' => 10
        ]);

        Cart::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.rates'), [
            'address_id' => $address->id
        ]);

        $response->assertStatus(200);
    }

    /**
     * Admin can view shipping settings page
     */
    public function test_admin_can_access_shipping_settings()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.shipping_settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Pengiriman (Biteship)');
    }

    /**
     * Admin can update shipping settings
     */
    public function test_admin_can_update_shipping_settings()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('admin.shipping_settings.update'), [
            'biteship_api_key' => 'biteship_test_key_123',
            'biteship_base_url' => 'https://api.biteship.com',
            'biteship_origin_id' => 'biteship_origin_123',
        ]);

        $response->assertRedirect(route('admin.shipping_settings.index'));
        $this->assertEquals('biteship_test_key_123', Setting::getVal('biteship_api_key'));
        $this->assertEquals('https://api.biteship.com', Setting::getVal('biteship_base_url'));
        $this->assertEquals('biteship_origin_123', Setting::getVal('biteship_origin_id'));
    }

    /**
     * Admin can test shipping connection
     */
    public function test_admin_can_test_connection()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('admin.shipping_settings.test'), [
            'biteship_api_key' => 'biteship_test_key_123',
            'biteship_base_url' => 'https://api.biteship.com',
        ]);

        $response->assertRedirect();
    }

    /**
     * Customer can cancel pending order
     */
    public function test_customer_can_cancel_pending_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'invoice_number' => 'INV-TEST-CAN',
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'total_price' => 50000,
        ]);

        $response = $this->actingAs($customer)->delete(route('pesanan.destroy', $order->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'dibatalkan',
        ]);
    }

    /**
     * Customer cannot cancel paid order
     */
    public function test_customer_cannot_cancel_paid_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'invoice_number' => 'INV-TEST-NOCAN',
            'order_status' => 'diproses',
            'payment_status' => 'settlement',
            'total_price' => 50000,
        ]);

        $response = $this->actingAs($customer)->delete(route('pesanan.destroy', $order->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'diproses',
        ]);
    }

    /**
     * Production dashboard (Kelola Pesanan) shows all orders
     */
    public function test_production_dashboard_shows_all_orders()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Paid order
        $paidOrder = Order::create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-PAID-OK',
            'order_status' => 'diproses',
            'payment_status' => 'settlement',
            'total_price' => 100000,
        ]);

        // Unpaid pending order
        $pendingOrder = Order::create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-PENDING-NO',
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'total_price' => 100000,
        ]);

        $response = $this->actingAs($admin)->get(route('pesanan.index'));
        $response->assertStatus(200);
        $response->assertSee('INV-PAID-OK');
        $response->assertSee('INV-PENDING-NO');
    }

    /**
     * Test pickup order workflow (Siap Diambil, Sudah Diambil, and Customer Terima)
     */
    public function test_pickup_order_workflow()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        // Create a pickup order in "dikemas" state
        $order = Order::create([
            'user_id' => $customer->id,
            'invoice_number' => 'INV-PICKUP-123',
            'order_status' => 'dikemas',
            'payment_status' => 'settlement',
            'total_price' => 50000,
            'shipping_courier' => 'pickup',
            'shipping_service' => 'Ambil di Toko',
            'shipping_cost' => 0,
        ]);

        // 1. Admin marks as "Siap Diambil"
        $response = $this->actingAs($admin)->post(route('admin.shipping.shipments.create', $order->id));
        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'siap_diambil',
        ]);

        // 2. Customer marks as received ("Saya Sudah Mengambil Pesanan")
        $response = $this->actingAs($customer)->post(route('pesanan.terima', $order->id));
        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'selesai',
        ]);
    }
}
