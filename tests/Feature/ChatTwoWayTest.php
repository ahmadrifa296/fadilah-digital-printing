<?php

namespace Tests\Feature;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTwoWayTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_way_chat_between_customer_and_admin()
    {
        // 1. Create Admin & Customer Users
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'role' => 'customer',
        ]);

        // 2. Customer sends a message via /chat/send
        $response = $this->actingAs($customer)->postJson(route('chat.send'), [
            'message' => 'Halo Admin, apakah spanduk ready?',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify message saved in DB & unread_admin = 1
        $room = ChatRoom::where('customer_id', $customer->id)->first();
        $this->assertNotNull($room);
        $this->assertDatabaseHas('chat_messages', [
            'room_id' => $room->id,
            'sender_id' => $customer->id,
            'message' => 'Halo Admin, apakah spanduk ready?',
        ]);

        // 3. Admin polls room list via /admin/chat-rooms-poll
        $roomsResponse = $this->actingAs($admin)->getJson(route('chat.rooms.poll'));
        $roomsResponse->assertStatus(200);
        $roomsResponse->assertJsonFragment([
            'customer_id' => $customer->id,
            'customer_name' => 'Customer Test',
        ]);

        // 4. Admin opens room via /admin/chat/room/{room_id}
        $showResponse = $this->actingAs($admin)->getJson(route('admin.show', $room->id));
        $showResponse->assertStatus(200);
        $showResponse->assertJsonFragment([
            'message' => 'Halo Admin, apakah spanduk ready?',
            'is_mine' => false, // From admin perspective, customer message is NOT mine
        ]);

        // 5. Admin replies to customer via /reply
        $replyResponse = $this->actingAs($admin)->postJson(route('admin.reply'), [
            'room_id' => $room->id,
            'message' => 'Halo Kak Customer, spanduk ready stok banyak ya!',
        ]);

        $replyResponse->assertStatus(200);
        $replyResponse->assertJson(['success' => true]);

        // Verify reply saved in DB
        $this->assertDatabaseHas('chat_messages', [
            'room_id' => $room->id,
            'sender_id' => $admin->id,
            'message' => 'Halo Kak Customer, spanduk ready stok banyak ya!',
        ]);

        // 6. Customer polls messages via /chat/poll
        $pollResponse = $this->actingAs($customer)->getJson(route('chat.poll'));
        $pollResponse->assertStatus(200);
        $pollResponse->assertJsonFragment([
            'message' => 'Halo Kak Customer, spanduk ready stok banyak ya!',
            'is_mine' => false, // From customer perspective, admin reply is NOT mine
        ]);
    }
}
