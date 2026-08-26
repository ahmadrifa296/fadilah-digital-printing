<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuyNowTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $product;
    protected UserAddress $address;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base data
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
            'requires_design_file' => true,
        ]);

        $this->address = UserAddress::create([
            'user_id' => $this->customer->id,
            'receiver_name' => 'John Doe',
            'phone' => '08123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Barat',
            'district' => 'Palmerah',
            'subdistrict' => 'Kemanggisan',
            'postal_code' => '11480',
            'rt' => '001',
            'rw' => '002',
            'no_rumah' => '10',
            'full_address' => 'Jl. Kemanggisan Raya No. 10',
            'label' => 'Kantor',
            'is_default' => true,
        ]);
    }

    public function test_guest_cannot_use_buy_now()
    {
        $response = $this->post(route('buy_now', $this->product->id), [
            'qty' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_buy_now_saves_to_session_and_redirects_to_direct_checkout()
    {
        $response = $this->actingAs($this->customer)
            ->post(route('buy_now', $this->product->id), [
                'qty' => 2,
                'ukuran' => '2x1 meter',
                'bahan' => 'Flexy 340g',
                'custom_text' => 'Cetak Spanduk Toko',
            ]);

        $response->assertRedirect(route('checkout.direct'));
        
        $this->assertTrue(session()->has('buy_now'));
        $buyNow = session('buy_now');
        $this->assertEquals($this->product->id, $buyNow['product_id']);
        $this->assertEquals(2, $buyNow['quantity']);
        $this->assertEquals('2x1 meter', $buyNow['options']['ukuran']);
    }

    public function test_direct_checkout_fails_validation_if_design_file_is_required_but_missing()
    {
        session([
            'buy_now' => [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'options' => [
                    'ukuran' => '2x1 meter',
                ]
            ]
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'is_direct' => '1',
                'address_id' => $this->address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'shipping_cost' => 15000,
                'shipping_estimation' => '1-2 Hari',
            ]);

        $response->assertSessionHasErrors(['design_file']);
    }

    public function test_direct_checkout_succeeds_with_design_file_and_saves_using_uuid()
    {
        Storage::fake('public');

        session([
            'buy_now' => [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'options' => [
                    'ukuran' => '2x1 meter',
                    'bahan' => 'Flexy 280g',
                ]
            ]
        ]);

        $file = UploadedFile::fake()->create('mydesign.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'is_direct' => '1',
                'address_id' => $this->address->id,
                'shipping_courier' => 'jne',
                'shipping_service' => 'reg',
                'shipping_cost' => 15000,
                'shipping_estimation' => '1-2 Hari',
                'design_file' => $file,
            ]);

        $response->assertRedirect();
        
        // Assert order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
        ]);

        $order = \App\Models\Order::first();
        $this->assertNotNull($order->design_file);
        
        // Assert session buy_now is forgotten
        $this->assertFalse(session()->has('buy_now'));

        // Assert file exists on public disk
        Storage::disk('public')->assertExists($order->design_file);
    }
}
