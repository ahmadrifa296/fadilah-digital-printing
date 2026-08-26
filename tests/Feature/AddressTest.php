<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access address page
     */
    public function test_guest_cannot_access_address_page()
    {
        $response = $this->get(route('addresses.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test customer can view their addresses page
     */
    public function test_customer_can_view_address_page()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('addresses.index'));

        $response->assertStatus(200);
        $response->assertSee('Alamat Saya');
    }

    /**
     * Test customer can add shipping address
     */
    public function test_customer_can_add_shipping_address()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post(route('addresses.store'), [
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'patokan' => 'Depan masjid',
            'full_address' => 'Jl. Melati No. 10, Coblong, Bandung',
            'notes' => 'Pagar hitam sebelah warung',
        ]);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'is_default' => true, // First address should automatically be default
        ]);
    }

    /**
     * Test customer can update their address
     */
    public function test_customer_can_update_address()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $address = UserAddress::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'patokan' => 'Depan masjid',
            'full_address' => 'Jl. Melati No. 10, Coblong, Bandung',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->put(route('addresses.update', $address->id), [
            'label' => 'Kantor',
            'receiver_name' => 'Ahmad Rifa (Kantor)',
            'phone' => '08129999999',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'subdistrict' => 'Senayan',
            'postal_code' => '12190',
            'rt' => '003',
            'rw' => '010',
            'no_rumah' => 'SCBD Lt 10',
            'patokan' => 'Samping halte',
            'full_address' => 'Gedung SCBD Lt 10, Sudirman, Jakarta',
            'is_default' => 1,
        ]);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'label' => 'Kantor',
            'receiver_name' => 'Ahmad Rifa (Kantor)',
            'phone' => '08129999999',
            'province' => 'DKI Jakarta',
        ]);
    }

    /**
     * Test customer can delete their address
     */
    public function test_customer_can_delete_address()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $address = UserAddress::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'patokan' => 'Depan masjid',
            'full_address' => 'Jl. Melati No. 10, Coblong, Bandung',
        ]);

        $response = $this->actingAs($user)->delete(route('addresses.destroy', $address->id));

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
    }

    /**
     * Test checkout is blocked if customer has no address
     */
    public function test_checkout_is_blocked_if_no_address()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['category_name' => 'Umum']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Kartu Nama',
            'price' => 50000,
            'stock' => 10,
            'product_type' => 'custom',
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => $product->price,
            'subtotal' => $product->price * 2,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Anda belum memiliki alamat pengiriman.');
        $response->assertDontSee('Buat Pesanan &amp; Bayar');
    }

    /**
     * Test checkout succeeds and copies address details to order if customer has an address
     */
    public function test_checkout_succeeds_and_copies_address_snapshot_to_order()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $address = UserAddress::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'patokan' => 'Depan masjid',
            'full_address' => 'Jl. Melati No. 10, Coblong, Bandung',
            'is_default' => true,
        ]);

        $category = Category::create(['category_name' => 'Umum']);
        $product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Kartu Nama',
            'price' => 50000,
            'stock' => 10,
            'product_type' => 'custom',
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 50000,
            'subtotal' => 100000,
        ]);

        // Submit checkout with selected address_id
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'notes' => 'Jangan lupa dikemas aman',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            'shipping_cost' => 15000,
            'shipping_estimation' => '2-3 hari',
        ]);

        // Should redirect to payment gateway screen
        $response->assertRedirect();
        
        // Assert order table contains snapshot of address
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 115000,
            'receiver_name' => 'Ahmad Rifa',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'subdistrict' => 'Dago',
            'postal_code' => '40135',
            'rt' => '002',
            'rw' => '015',
            'no_rumah' => 'No. 12B',
            'patokan' => 'Depan masjid',
            'full_address' => 'Jl. Melati No. 10, Coblong, Bandung',
            'address_label' => 'Rumah',
        ]);
    }

    /**
     * Test guest cannot access Indonesian regions proxy API
     */
    public function test_guest_cannot_access_indonesian_regions_proxy_api()
    {
        $this->get(route('api.address.provinces'))->assertRedirect(route('login'));
        $this->get(route('api.address.regencies', 32))->assertRedirect(route('login'));
        $this->get(route('api.address.districts', 3273))->assertRedirect(route('login'));
        $this->get(route('api.address.villages', 3273190))->assertRedirect(route('login'));
    }

    /**
     * Test customer can access provinces API proxy
     */
    public function test_customer_can_access_indonesian_regions_proxy_api()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('api.address.provinces'));
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }
}
