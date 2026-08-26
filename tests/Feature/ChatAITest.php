<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Setting;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatAITest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->admin = User::create([
            'name' => 'Admin Fadilah',
            'email' => 'admin@fadilah.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $category = Category::create([
            'category_name' => 'Brosur',
            'slug' => 'brosur',
        ]);

        // Create product
        $this->product = Product::create([
            'category_id' => $category->id,
            'product_name' => 'Brosur A4 Premium',
            'product_type' => 'custom',
            'price' => 15000,
            'stock' => 120,
            'weight' => 20,
            'length' => 21,
            'width' => 29,
            'height' => 1,
        ]);

        // Seed settings
        Setting::setVal('web_address', 'Jl. Percetakan Modern No. 45, Jakarta Selatan');
    }

    /**
     * Test AI replies to FAQ questions correctly.
     */
    public function test_ai_replies_to_faq_cara_order(): void
    {
        $this->actingAs($this->customer);
        $this->get(route('chat.index'));

        $response = $this->postJson(route('chat.send'), [
            'message' => 'Bagaimana cara order di toko ini?',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $this->customer->id,
            'message' => 'Bagaimana cara order di toko ini?',
        ]);

        // Should return FAQ from settings
        $expectedFAQ = Setting::getVal('faq_cara_order');
        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $this->admin->id,
            'message' => $expectedFAQ,
        ]);
    }

    /**
     * Test AI replies product specific details.
     */
    public function test_ai_replies_product_price_and_stock(): void
    {
        $this->actingAs($this->customer);
        $this->get(route('chat.index'));

        // Query price
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Berapa harga Brosur A4 Premium?',
        ]);

        $response->assertStatus(200);
        
        $aiReply = ChatMessage::where('sender_id', $this->admin->id)->latest('id')->first();
        $this->assertNotNull($aiReply);
        $this->assertStringContainsString('Brosur A4 Premium', $aiReply->message);
        $this->assertStringContainsString('Rp 15.000', $aiReply->message);
        $this->assertStringContainsString('120', $aiReply->message);
    }

    /**
     * Test AI Levenshtein similarity search.
     */
    public function test_ai_similarity_search(): void
    {
        $this->actingAs($this->customer);
        $this->get(route('chat.index'));

        // Search with typo
        $response = $this->postJson(route('chat.send'), [
            'message' => 'brosurr',
        ]);

        $response->assertStatus(200);

        $aiReply = ChatMessage::where('sender_id', $this->admin->id)->latest('id')->first();
        $this->assertNotNull($aiReply);
        $this->assertStringContainsString('Brosur A4 Premium', $aiReply->message);
    }

    /**
     * Test isolated "pesanan saya" retrieval.
     */
    public function test_ai_pesanan_saya_is_isolated(): void
    {
        // Order for current customer
        $order1 = Order::create([
            'user_id' => $this->customer->id,
            'invoice_number' => 'FDP-20260711-000001',
            'order_status' => 'diproses',
            'payment_status' => 'settlement',
            'total_price' => 50000,
        ]);

        // Order for another customer
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order2 = Order::create([
            'user_id' => $otherCustomer->id,
            'invoice_number' => 'FDP-20260711-000002',
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'total_price' => 100000,
        ]);

        $this->actingAs($this->customer);
        $this->get(route('chat.index'));

        $this->postJson(route('chat.send'), [
            'message' => 'pesanan saya',
        ]);

        $aiReply = ChatMessage::where('sender_id', $this->admin->id)->latest('id')->first();
        $this->assertNotNull($aiReply);
        
        // Assert customer sees their own invoice
        $this->assertStringContainsString('FDP-20260711-000001', $aiReply->message);
        
        // Assert customer DOES NOT see other customer's invoice (security isolation)
        $this->assertStringNotContainsString('FDP-20260711-000002', $aiReply->message);
    }

    /**
     * Test Admin Override pauses AI only if admin manually takes over (manual mode).
     * New behavior: AI replies even when admin is online.
     * AI is paused ONLY when admin manually sends a reply (sets chat_room_manual_ cache).
     */
    public function test_admin_override_pauses_ai_only_if_admin_is_online(): void
    {
        $this->actingAs($this->customer);
        $this->get(route('chat.index'));
        $room = ChatRoom::where('customer_id', $this->customer->id)->firstOrFail();

        // 1. Admin online but has NOT manually replied yet -> AI still replies
        $this->actingAs($this->admin);
        Cache::put('admin_last_seen', now(), 300); // Admin online

        $this->actingAs($this->customer);
        $this->postJson(route('chat.send'), [
            'message' => 'cara order',
        ]);

        // Welcome message (1) + customer query (2) + AI reply (3) = 3 messages
        // (AI replies even though admin is online, because no manual takeover)
        $this->assertEquals(3, ChatMessage::where('room_id', $room->id)->count());

        // 2. Admin manually replies -> sets chat_room_manual_ cache -> AI pauses
        $this->actingAs($this->admin);
        $this->postJson(route('admin.reply'), [
            'room_id' => $room->id,
            'message' => 'Silakan tunggu sebentar.',
        ]);

        // Now manual mode is active. Customer sends another message -> AI should NOT reply
        $this->actingAs($this->customer);
        $this->postJson(route('chat.send'), [
            'message' => 'cara order',
        ]);

        // 3 + admin reply (4) + customer query (5) = 5, no AI reply because manual mode
        $this->assertEquals(5, ChatMessage::where('room_id', $room->id)->count());
    }

    /**
     * Test Avatar Resolution Priorities.
     */
    public function test_admin_avatar_priorities(): void
    {
        // Clear settings and admin avatar
        $this->admin->update(['avatar' => null]);
        Setting::where('key', 'company_logo')->delete();
        Setting::where('key', 'web_logo')->delete();

        // Priority 3: Fallback ui-avatars
        $avatar = \App\Http\Controllers\ChatRoomController::resolveAdminAvatar();
        $this->assertStringContainsString('ui-avatars.com', $avatar);

        // Priority 2: Company logo
        Setting::setVal('company_logo', '/storage/settings/logo.png');
        $avatar = \App\Http\Controllers\ChatRoomController::resolveAdminAvatar();
        $this->assertStringContainsString('logo.png', $avatar);

        // Priority 1: Admin avatar
        $this->admin->update(['avatar' => 'profile/admin.jpg']);
        $avatar = \App\Http\Controllers\ChatRoomController::resolveAdminAvatar();
        $this->assertStringContainsString('admin.jpg', $avatar);
    }
}
