<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_room_id),
        ];
    }

    public function broadcastWith(): array
    {
        $this->message->load(['sender', 'attachment']);
        return [
            'id' => $this->message->id,
            'chat_room_id' => $this->message->chat_room_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message_text' => $this->message->message_text,
            'message_type' => $this->message->message_type,
            'linked_type' => $this->message->linked_type,
            'linked_id' => $this->message->linked_id,
            'created_at' => $this->message->created_at->toISOString(),
            'attachment' => $this->message->attachment ? [
                'file_name' => $this->message->attachment->file_name,
                'file_url' => $this->message->attachment->file_url,
                'file_size' => $this->message->attachment->file_size,
                'file_type' => $this->message->attachment->file_type,
            ] : null,
        ];
    }
}
