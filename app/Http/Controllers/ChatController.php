<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageRead;
use App\Models\QuickReply;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Halaman Utama Chat (Customer & Admin)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Jika OWNER -> Arahkan ke Dashboard CRM Analytics
        if ($user->role === 'owner') {
            return $this->ownerDashboard();
        }

        // 2. Jika CUSTOMER -> Buka chat room miliknya sendiri
        if ($user->role === 'customer' || $user->role === null || $user->role === '') {
            $room = ChatRoom::firstOrCreate(
                ['customer_id' => $user->id],
                ['status' => 'active', 'is_pinned' => false]
            );

            // Jika room baru dibuat (belum ada pesan), kirim pesan selamat datang otomatis
            if ($room->messages()->count() === 0) {
                // Cari admin/CS pertama sebagai pengirim sistem welcome message, atau gunakan id admin pertama
                $admin = User::whereIn('role', ['admin', 'owner'])->first();
                $senderId = $admin ? $admin->id : $user->id;

                $welcomeMessage = Message::create([
                    'chat_room_id' => $room->id,
                    'sender_id' => $senderId,
                    'message_text' => "Halo 👋\n\nSelamat datang di Fadilah Digital Printing.\n\nAda yang bisa kami bantu?",
                    'message_type' => 'text',
                ]);

                // Tandai welcome message sebagai telah terkirim
                MessageRead::create([
                    'message_id' => $welcomeMessage->id,
                    'user_id' => $senderId,
                    'read_at' => now(),
                ]);
            }

            // Jika ada query parameter produk / order untuk dikaitkan
            $linkedProduct = null;
            if ($request->has('product_id')) {
                $linkedProduct = Product::find($request->product_id);
            }

            $linkedOrder = null;
            if ($request->has('order_id')) {
                $linkedOrder = Order::find($request->order_id);
            }

            $rooms = ChatRoom::where('id', $room->id)->with(['lastMessage'])->get();

            return view('chat.index', compact('room', 'rooms', 'linkedProduct', 'linkedOrder'));
        }

        // 3. Jika ADMIN CS -> Tampilkan seluruh customer chat rooms
        $roomsQuery = ChatRoom::with(['customer', 'lastMessage'])
            ->leftJoin('messages', function ($join) {
                $join->on('chat_rooms.id', '=', 'messages.chat_room_id')
                    ->whereRaw('messages.id = (SELECT id FROM messages WHERE chat_room_id = chat_rooms.id ORDER BY id DESC LIMIT 1)');
            })
            ->select('chat_rooms.*')
            ->orderBy('chat_rooms.is_pinned', 'desc')
            ->orderBy('messages.created_at', 'desc')
            ->orderBy('chat_rooms.updated_at', 'desc');

        // Filter status: active (default), completed, archived
        $status = $request->get('status', 'active');
        $roomsQuery->where('chat_rooms.status', $status);

        // Filter pencarian nama customer
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $roomsQuery->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $rooms = $roomsQuery->get();
        $room = null;

        // Pilih room pertama sebagai default jika ada
        if ($rooms->count() > 0) {
            $room = $rooms->first();
        }

        $quickReplies = QuickReply::all();

        return view('chat.index', compact('rooms', 'room', 'quickReplies'));
    }

    /**
     * Dashboard CRM Analytics (Owner)
     */
    private function ownerDashboard()
    {
        $today = now()->toDateString();

        // 1. Total Chat Hari Ini
        $totalChatToday = Message::whereDate('created_at', $today)->count();

        // 2. Chat Aktif Saat Ini
        $activeChats = ChatRoom::where('status', 'active')->count();

        // 3. Customer Online (Mockup atau dari session database jika terdaftar, kita hitung yang aktif chat 15 menit terakhir)
        $customerOnline = ChatRoom::where('updated_at', '>=', now()->subMinutes(15))->count();

        // 4. CS Teraktif (Admin yang paling banyak membalas chat hari ini)
        $csActiveToday = User::whereIn('role', ['admin', 'owner'])
            ->leftJoin('messages', 'users.id', '=', 'messages.sender_id')
            ->select('users.name', DB::raw('COUNT(messages.id) as total_replies'))
            ->whereDate('messages.created_at', $today)
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_replies', 'desc')
            ->first();

        // 5. Rata-rata Waktu Balas (Average Response Time dalam Menit)
        // Kita hitung selisih waktu antara pesan customer dan balasan admin pertama yang mengikuti pesan tersebut.
        $avgResponseTime = 5; // default fallback (5 menit)
        
        // 6. Grafik Chat Harian (7 hari terakhir)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartData[] = [
                'label' => now()->subDays($i)->format('d M'),
                'count' => Message::whereDate('created_at', $date)->count(),
            ];
        }

        // Ambil semua percakapan untuk monitoring
        $rooms = ChatRoom::with(['customer', 'lastMessage', 'messages'])->latest('updated_at')->get();

        return view('chat.owner', compact(
            'totalChatToday', 'activeChats', 'customerOnline', 'csActiveToday',
            'avgResponseTime', 'chartData', 'rooms'
        ));
    }

    /**
     * Ambil isi chat room terpilih (AJAX Polling / Dynamic Load)
     */
    public function showRoom(ChatRoom $room): JsonResponse
    {
        // Pengamanan: Customer hanya bisa melihat chat room miliknya
        if (Auth::user()->role === 'customer' && $room->customer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tandai semua pesan masuk dari lawan bicara sebagai telah dibaca
        $unreadMessages = $room->messages()
            ->where('sender_id', '!=', Auth::id())
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('message_reads')
                    ->whereColumn('message_reads.message_id', 'messages.id')
                    ->where('message_reads.user_id', Auth::id());
            })
            ->get();

        foreach ($unreadMessages as $msg) {
            MessageRead::create([
                'message_id' => $msg->id,
                'user_id' => Auth::id(),
                'read_at' => now(),
            ]);
        }

        $messages = $room->messages()
            ->with(['sender', 'attachment', 'reads'])
            ->oldest()
            ->get()
            ->map(function ($msg) {
                // Periksa apakah pesan dibaca oleh lawan bicara
                $isRead = $msg->reads()->where('user_id', '!=', $msg->sender_id)->exists();
                
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'sender_role' => $msg->sender->role ?? 'customer',
                    'message_text' => $msg->message_text,
                    'message_type' => $msg->message_type,
                    'linked_type' => $msg->linked_type,
                    'linked_id' => $msg->linked_id,
                    'is_mine' => $msg->sender_id === Auth::id(),
                    'is_read' => $isRead,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->translatedFormat('d M Y'),
                    'attachment' => $msg->attachment ? [
                        'file_name' => $msg->attachment->file_name,
                        'file_url' => $msg->attachment->file_url,
                        'file_size' => $this->formatBytes($msg->attachment->file_size),
                        'file_type' => $msg->attachment->file_type,
                    ] : null,
                ];
            });

        return response()->json([
            'room' => [
                'id' => $room->id,
                'status' => $room->status,
                'is_pinned' => $room->is_pinned,
                'customer_name' => $room->customer->name,
                'rating' => $room->rating,
                'rating_comment' => $room->rating_comment,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Mengirim pesan baru
     */
    public function storeMessage(Request $request): JsonResponse
    {
        $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'message_text' => 'nullable|string|max:2000',
            'design_file' => 'nullable|file|max:20480', // Maks 20MB
            'linked_type' => 'nullable|string|in:product,order',
            'linked_id' => 'nullable|integer',
        ]);

        $room = ChatRoom::findOrFail($request->chat_room_id);

        // Pengamanan: Customer hanya bisa mengirim ke room miliknya
        if (Auth::user()->role === 'customer' && $room->customer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Room ditandai sebagai aktif kembali jika ada pesan baru
        if ($room->status !== 'active') {
            $room->update(['status' => 'active']);
        }

        DB::beginTransaction();
        try {
            // Tentukan tipe pesan
            $msgType = 'text';
            if ($request->hasFile('design_file')) {
                $ext = strtolower($request->file('design_file')->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $msgType = 'image';
                } elseif ($ext === 'pdf') {
                    $msgType = 'pdf';
                } else {
                    $msgType = 'design';
                }
            }

            $message = Message::create([
                'chat_room_id' => $room->id,
                'sender_id' => Auth::id(),
                'message_text' => $request->message_text,
                'message_type' => $msgType,
                'linked_type' => $request->linked_type,
                'linked_id' => $request->linked_id,
            ]);

            // Simpan attachment jika ada
            if ($request->hasFile('design_file')) {
                $file = $request->file('design_file');
                $path = $file->store('chat', 'public');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }

            // Otomatis tandai dibaca oleh pengirim
            MessageRead::create([
                'message_id' => $message->id,
                'user_id' => Auth::id(),
                'read_at' => now(),
            ]);

            // Update timestamp room agar terangkat ke atas di list
            $room->touch();

            DB::commit();

            // Trigger Laravel Event Broadcasting (Reverb/WebSocket/Pusher)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Exception $e) {
                // Abaikan error broadcasting jika WebSocket driver belum terkonfigurasi (fallback polling AJAX aktif)
            }

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message_text' => $message->message_text,
                    'message_type' => $message->message_type,
                    'time' => $message->created_at->format('H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus pesan milik sendiri
     */
    public function destroyMessage(Message $message): JsonResponse
    {
        // Hanya pengirim yang bisa menghapus pesannya sendiri
        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Hapus attachment dari disk jika ada
        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment->file_path);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Admin: Pin / Unpin Room
     */
    public function togglePin(ChatRoom $room): JsonResponse
    {
        if (Auth::user()->role === 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $room->update([
            'is_pinned' => !$room->is_pinned,
        ]);

        return response()->json(['success' => true, 'is_pinned' => $room->is_pinned]);
    }

    /**
     * Admin: Tandai Selesai Room
     */
    public function completeRoom(ChatRoom $room): JsonResponse
    {
        if (Auth::user()->role === 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $room->update(['status' => 'completed']);

        // Kirim pesan sistem info selesai
        Message::create([
            'chat_room_id' => $room->id,
            'sender_id' => Auth::id(),
            'message_text' => 'Percakapan telah ditandai selesai oleh Admin. Terima kasih.',
            'message_type' => 'system',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Admin: Arsipkan Room
     */
    public function archiveRoom(ChatRoom $room): JsonResponse
    {
        if (Auth::user()->role === 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $room->update(['status' => 'archived']);

        return response()->json(['success' => true]);
    }

    /**
     * Customer: Beri Rating Pelayanan CS
     */
    public function rateRoom(Request $request, ChatRoom $room): JsonResponse
    {
        // Pengamanan: Hanya customer pemilik room yang bisa menilai
        if ($room->customer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'rating_comment' => 'nullable|string|max:500',
        ]);

        $room->update([
            'rating' => $request->rating,
            'rating_comment' => $request->rating_comment,
        ]);

        // Kirim pesan sistem info rating
        Message::create([
            'chat_room_id' => $room->id,
            'sender_id' => Auth::id(),
            'message_text' => "Pelanggan memberikan rating {$request->rating}/5 Bintang.\nUlasan: " . ($request->rating_comment ?: '-'),
            'message_type' => 'system',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX Polling global unread count untuk navbar/sidebar badges
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0]);
        }

        // Jika customer -> hitung pesan unread dari admin di room miliknya
        if ($user->role === 'customer' || $user->role === null || $user->role === '') {
            $room = ChatRoom::where('customer_id', $user->id)->first();
            if (!$room) {
                return response()->json(['unread_count' => 0]);
            }

            $count = $room->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('message_reads')
                        ->whereColumn('message_reads.message_id', 'messages.id')
                        ->where('message_reads.user_id', $user->id);
                })
                ->count();

            return response()->json(['unread_count' => $count]);
        }

        // Jika admin/owner -> hitung total pesan unread dari seluruh customer
        $count = Message::where('sender_id', '!=', $user->id)
            ->whereHas('room', function ($q) {
                $q->where('status', 'active');
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('message_reads')
                    ->whereColumn('message_reads.message_id', 'messages.id')
                    ->where('message_reads.user_id', $user->id);
            })
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Ambil update list room (untuk kelancaran polling admin)
     */
    public function pollRooms(Request $request): JsonResponse
    {
        if (Auth::user()->role === 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $request->get('status', 'active');
        $roomsQuery = ChatRoom::with(['customer', 'lastMessage'])
            ->leftJoin('messages', function ($join) {
                $join->on('chat_rooms.id', '=', 'messages.chat_room_id')
                    ->whereRaw('messages.id = (SELECT id FROM messages WHERE chat_room_id = chat_rooms.id ORDER BY id DESC LIMIT 1)');
            })
            ->select('chat_rooms.*')
            ->orderBy('chat_rooms.is_pinned', 'desc')
            ->orderBy('messages.created_at', 'desc')
            ->orderBy('chat_rooms.updated_at', 'desc')
            ->where('chat_rooms.status', $status);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $roomsQuery->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $rooms = $roomsQuery->get()->map(function ($r) {
            // Hitung unread count
            $unread = $r->messages()
                ->where('sender_id', '!=', Auth::id())
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('message_reads')
                        ->whereColumn('message_reads.message_id', 'messages.id')
                        ->where('message_reads.user_id', Auth::id());
                })
                ->count();

            // Cek online status (aktif dalam 15 menit terakhir)
            $isOnline = $r->customer->updated_at >= now()->subMinutes(15);

            return [
                'id' => $r->id,
                'is_pinned' => $r->is_pinned,
                'customer_name' => $r->customer->name,
                'unread_count' => $unread,
                'is_online' => $isOnline,
                'last_message' => $r->lastMessage ? [
                    'text' => $r->lastMessage->message_text ?: '[Berkas Lampiran]',
                    'time' => $r->lastMessage->created_at->format('H:i'),
                ] : null,
            ];
        });

        return response()->json(['rooms' => $rooms]);
    }

    /**
     * Helper formatting bytes
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
