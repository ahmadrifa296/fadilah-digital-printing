<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatTemplate;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatRoomController extends Controller
{
    /**
     * Customer: Buka Room Chat Tunggal Permanen
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cek jika Admin / Owner -> alihkan ke workspace masing-masing
        if ($user->role === 'admin') {
            \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);
            return redirect()->route('admin.chat');
        } elseif ($user->role === 'owner') {
            return redirect()->route('owner.chat');
        }

        // Customer: Pastikan room chat sudah ada (buat jika belum)
        $room = ChatRoom::firstOrCreate(
            ['customer_id' => $user->id],
            [
                'status' => 'open',
                'unread_customer' => 0,
                'unread_admin' => 0
            ]
        );

        // Jika room baru dibuat (belum ada pesan), kirim pesan selamat datang otomatis
        if ($room->messages()->count() === 0) {
            $admin = User::where('role', 'admin')->first();
            $senderId = $admin ? $admin->id : $user->id;

            ChatMessage::create([
                'room_id' => $room->id,
                'sender_id' => $senderId,
                'message' => "Halo 👋\n\nSelamat datang di Fadilah Digital Printing.\n\nAda yang bisa kami bantu?",
                'is_read' => true
            ]);
        }

        // Redirect ke dashboard dan buka widget chat otomatis
        // Simpan linked product/order ke session jika ada
        if ($request->has('product_id')) {
            $product = \App\Models\Product::find($request->product_id);
            if ($product) {
                session()->flash('chat_linked_product', json_encode([
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'price' => $product->price,
                    'image_url' => $product->image ? asset('storage/' . $product->image) : 'https://placehold.co/100'
                ]));
            }
        }

        if ($request->has('order_id')) {
            $order = Order::find($request->order_id);
            if ($order) {
                session()->flash('chat_linked_order', json_encode([
                    'id' => $order->id,
                    'invoice' => $order->invoice_number,
                    'status' => $order->order_status
                ]));
            }
        }

        return redirect()->route('dashboard')->with('open_chat', true);
    }

    /**
     * Customer: Kirim pesan
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required_without:attachment|nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,rar,ai,psd,cdr|max:10240', // Maks 10MB
        ]);

        $user = Auth::user();
        $room = ChatRoom::firstOrCreate(
            ['customer_id' => $user->id],
            [
                'status' => 'open',
                'unread_customer' => 0,
                'unread_admin' => 0
            ]
        );

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            $attachmentType = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('chat', 'public');
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $attachmentType = 'image';
                } else {
                    $attachmentType = $ext;
                }
            }

            $message = ChatMessage::create([
                'room_id' => $room->id,
                'sender_id' => $user->id,
                'message' => $request->message ?? '',
                'attachment' => $attachmentPath,
                'attachment_type' => $attachmentType,
                'is_read' => false,
            ]);

            // Check if admin is currently online
            $lastSeen = \Illuminate\Support\Facades\Cache::get('admin_last_seen');
            $isAdminOnline = $lastSeen && $lastSeen->diffInMinutes(now()) < 5;

            // AI is paused ONLY if admin manually replied (sets manual_ cache) or explicitly toggled off
            $isManualMode = \Illuminate\Support\Facades\Cache::has('chat_room_manual_' . $room->id);
            $isAiPaused = \Illuminate\Support\Facades\Cache::has('chat_room_ai_paused_' . $room->id) || $isManualMode;

            // If admin has manually taken over → just notify admin, no AI reply
            if ($isAiPaused) {
                $room->increment('unread_admin');
            } else if (!empty($request->message)) {
                // AI always tries to reply unless manually paused
                $aiService = new \App\Services\ChatAIService();
                $replyText = $aiService->getReply($request->message, $user);

                $admin = User::where('role', 'admin')->first();
                $adminId = $admin ? $admin->id : 1;

                if ($replyText !== null) {
                    ChatMessage::create([
                        'room_id' => $room->id,
                        'sender_id' => $adminId,
                        'message' => $replyText,
                        'is_read' => false,
                    ]);
                    $room->increment('unread_customer');
                    // If admin is online, also notify them about new message
                    if ($isAdminOnline) {
                        $room->increment('unread_admin');
                    }
                } else {
                    // No AI answer: forward to admin
                    $room->increment('unread_admin');
                }
            } else {
                $room->increment('unread_admin');
            }

            $room->touch(); // Update updated_at untuk daftar urutan admin

            // Kirim notifikasi ke admin/owner
            $admins = User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AppNotification(
                    'Pesan Chat Baru',
                    "Customer '{$user->name}' mengirim pesan: " . \Illuminate\Support\Str::limit($message->message, 50),
                    'chat',
                    'blue',
                    route('admin.chat') . "?room=" . $room->id,
                    'chat'
                ));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'is_mine' => true,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Customer: Polling Pesan Baru (setiap 3 detik)
     */
     public function pollCustomer(): JsonResponse
     {
         $user = Auth::user();
         $room = ChatRoom::firstOrCreate(
             ['customer_id' => $user->id],
             [
                 'status' => 'open',
                 'unread_customer' => 0,
                 'unread_admin' => 0
             ]
         );

         // Reset unread customer karena layar sedang aktif dibuka
         $room->update(['unread_customer' => 0]);
 
         // Admin status and avatar
         $lastSeen = \Illuminate\Support\Facades\Cache::get('admin_last_seen');
         $adminStatus = 'offline';
         $adminStatusText = 'Terakhir aktif 5 menit lalu';
 
         if ($lastSeen) {
             $diffInMinutes = $lastSeen->diffInMinutes(now());
             if ($diffInMinutes < 5) {
                 $adminStatus = 'online';
                 $adminStatusText = 'Online &middot; Customer Service';
             } else {
                 $adminStatusText = 'Terakhir aktif ' . $diffInMinutes . ' menit lalu';
             }
         }
 
         $adminAvatar = self::resolveAdminAvatar();
 
         $messages = $room->messages()
             ->with(['sender'])
             ->oldest()
             ->get()
             ->map(function ($msg) {
                 return [
                     'id' => $msg->id,
                     'sender_id' => $msg->sender_id,
                     'sender_name' => $msg->sender ? $msg->sender->name : 'User',
                     'sender_role' => $msg->sender ? ($msg->sender->role ?? 'customer') : 'customer',
                     'sender_avatar' => $msg->sender ? $msg->sender->avatar_url : '',
                     'message' => $msg->message,
                     'is_mine' => $msg->sender_id === Auth::id(),
                     'is_read' => $msg->is_read,
                     'time' => $msg->created_at->format('H:i'),
                     'date' => $msg->created_at->translatedFormat('d M Y'),
                     'attachment' => $msg->attachment ? [
                         'file_name' => basename($msg->attachment),
                         'file_url' => $msg->attachment_url,
                         'file_type' => $msg->attachment_type,
                     ] : null,
                 ];
             });
 
         return response()->json([
             'room' => [
                 'id' => $room->id,
                 'status' => $room->status,
                 'admin_status' => $adminStatus,
                 'admin_status_text' => $adminStatusText,
                 'admin_avatar' => $adminAvatar,
             ],
             'messages' => $messages,
         ]);
     }

    /**
     * Admin: Daftar Customer Chat (Workspace)
     */
    public function adminIndex()
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403);
        }

        \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);

        // Ambil room diurutkan berdasarkan chat masuk terbaru
        $rooms = ChatRoom::with(['customer', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $quickReplies = ChatTemplate::all();

        return view('chat.admin', compact('rooms', 'quickReplies'));
    }

    /**
     * Admin: Polling Daftar Customer Chat (Workspace) secara realtime
     */
    public function adminPollRooms(): JsonResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);

        $rooms = ChatRoom::with(['customer', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'customer_id' => $r->customer_id,
                    'customer_name' => $r->customer ? $r->customer->name : 'Customer',
                    'customer_avatar' => $r->customer ? $r->customer->avatar_url : '',
                    'unread_admin' => $r->unread_admin,
                    'last_message' => $r->lastMessage ? [
                        'text' => $r->lastMessage->message ?: '[Lampiran Berkas]',
                        'time' => $r->lastMessage->created_at->format('H:i'),
                    ] : null,
                ];
            });

        return response()->json(['rooms' => $rooms]);
    }

    /**
     * Admin: Buka Chat Room Customer & Mengambil Data Integrasi CRM
     */
    public function adminShow(ChatRoom $room): JsonResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);

        $customer = $room->customer;

        // Tandai pesan belum dibaca dari customer sebagai "read"
        $room->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Reset unread admin
        $room->update(['unread_admin' => 0]);

        $messages = $room->messages()
            ->with(['sender'])
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'User',
                    'sender_role' => $msg->sender ? ($msg->sender->role ?? 'customer') : 'customer',
                    'sender_avatar' => $msg->sender ? $msg->sender->avatar_url : '',
                    'message' => $msg->message,
                    'is_mine' => ($msg->sender && in_array($msg->sender->role, ['admin', 'owner'])) || $msg->sender_id === Auth::id(),
                    'is_read' => $msg->is_read,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->translatedFormat('d M Y'),
                    'attachment' => $msg->attachment ? [
                        'file_name' => basename($msg->attachment),
                        'file_url' => $msg->attachment_url,
                        'file_type' => $msg->attachment_type,
                    ] : null,
                ];
            });

        // INTEGRASI PESANAN (CRM DATA)
        $ordersQuery = Order::where('user_id', $customer ? $customer->id : 0);
        $totalOrders = $ordersQuery->count();
        $latestOrder = $ordersQuery->latest('id')->first();
        
        $defaultAddress = $customer ? ($customer->addresses()->where('is_default', true)->first() ?? $customer->addresses()->first()) : null;
        $totalSpent = Order::where('user_id', $customer ? $customer->id : 0)->whereIn('order_status', ['diproses', 'dicetak', 'dikirim', 'selesai'])->sum('total_price');
        $phone = $customer ? ($customer->phone_number ?: ($customer->phone ?: '-')) : '-';

        $crmData = [
            'name' => $customer ? $customer->name : 'Pelanggan',
            'email' => $customer ? $customer->email : '-',
            'phone' => $phone,
            'avatar' => $customer ? $customer->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($customer ? $customer->name : 'Customer') . '&color=ea580c&background=ffedd5',
            'member_since' => $customer ? $customer->created_at->translatedFormat('d M Y') : '-',
            'address' => $defaultAddress ? ($defaultAddress->address_label . ': ' . $defaultAddress->full_address) : '-',
            'total_orders' => $totalOrders,
            'total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
            'latest_invoice' => $latestOrder ? $latestOrder->invoice_number : '-',
            'latest_invoice_id' => $latestOrder ? $latestOrder->id : null,
            'latest_status' => $latestOrder ? ucfirst($latestOrder->order_status instanceof \App\Enums\OrderStatus ? $latestOrder->order_status->value : (string)$latestOrder->order_status) : '-',
            'latest_tracking' => $latestOrder ? ($latestOrder->tracking_status_label ?? 'Menunggu Konfirmasi') : '-',
            'latest_total' => $latestOrder ? 'Rp ' . number_format($latestOrder->total_price, 0, ',', '.') : '-',
        ];

        $isAiPaused = \Illuminate\Support\Facades\Cache::has('chat_room_ai_paused_' . $room->id) ||
                      \Illuminate\Support\Facades\Cache::has('chat_room_manual_' . $room->id);

        return response()->json([
            'room' => [
                'id' => $room->id,
                'status' => $room->status,
                'customer_name' => $customer->name,
                'is_ai_paused' => $isAiPaused,
            ],
            'messages' => $messages,
            'crm' => $crmData,
        ]);
    }

    /**
     * Admin: Balas Pesan Customer
     */
    public function adminReply(Request $request): JsonResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'room_id' => 'required|exists:chat_rooms,id',
            'message' => 'required_without:attachment|nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,rar,ai,psd,cdr|max:10240', // Maks 10MB
        ]);

        $room = ChatRoom::findOrFail($request->room_id);

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            $attachmentType = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('chat', 'public');
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $attachmentType = 'image';
                } else {
                    $attachmentType = $ext;
                }
            }

            $message = ChatMessage::create([
                'room_id' => $room->id,
                'sender_id' => Auth::id(),
                'message' => $request->message ?? '',
                'attachment' => $attachmentPath,
                'attachment_type' => $attachmentType,
                'is_read' => false,
            ]);

            // Set admin_id pada room untuk mencatat siapa admin yang aktif melayani
            $room->update([
                'admin_id' => Auth::id()
            ]);

            \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);
            \Illuminate\Support\Facades\Cache::put('chat_room_ai_paused_' . $room->id, true, now()->addMinutes(30));

            // Tambahkan unread customer
            $room->increment('unread_customer');
            $room->touch();

            // Kirim notifikasi ke customer
            $customer = $room->customer;
            if ($customer) {
                $customer->notify(new \App\Notifications\AppNotification(
                    'Pesan Chat Baru',
                    "Admin mengirim pesan: " . \Illuminate\Support\Str::limit($message->message, 50),
                    'chat',
                    'orange',
                    route('chat.index'),
                    'chat'
                ));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'is_mine' => true,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal mengirim balasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Polling Room Tertentu (setiap 3 detik)
     */
    public function pollAdmin(ChatRoom $room): JsonResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\Cache::put('admin_last_seen', now(), 300);

        // Tandai pesan customer sebagai dibaca karena admin aktif membukanya
        $room->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Reset unread admin
        $room->update(['unread_admin' => 0]);

        $messages = $room->messages()
            ->with(['sender'])
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'User',
                    'sender_role' => $msg->sender ? ($msg->sender->role ?? 'customer') : 'customer',
                    'sender_avatar' => $msg->sender ? $msg->sender->avatar_url : '',
                    'message' => $msg->message,
                    'is_mine' => ($msg->sender && in_array($msg->sender->role, ['admin', 'owner'])) || $msg->sender_id === Auth::id(),
                    'is_read' => $msg->is_read,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->translatedFormat('d M Y'),
                    'attachment' => $msg->attachment ? [
                        'file_name' => basename($msg->attachment),
                        'file_url' => $msg->attachment_url,
                        'file_type' => $msg->attachment_type,
                    ] : null,
                ];
            });

        $customer = $room->customer;
        $ordersQuery = Order::where('user_id', $customer ? $customer->id : 0);
        $totalOrders = $ordersQuery->count();
        $latestOrder = $ordersQuery->latest('id')->first();
        
        $defaultAddress = $customer ? ($customer->addresses()->where('is_default', true)->first() ?? $customer->addresses()->first()) : null;
        $totalSpent = Order::where('user_id', $customer ? $customer->id : 0)->whereIn('order_status', ['diproses', 'dicetak', 'dikirim', 'selesai'])->sum('total_price');
        $phone = $customer ? ($customer->phone_number ?: ($customer->phone ?: '-')) : '-';

        $crmData = [
            'name' => $customer ? $customer->name : 'Pelanggan',
            'email' => $customer ? $customer->email : '-',
            'phone' => $phone,
            'avatar' => $customer ? $customer->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($customer ? $customer->name : 'Customer') . '&color=ea580c&background=ffedd5',
            'member_since' => $customer ? $customer->created_at->translatedFormat('d M Y') : '-',
            'address' => $defaultAddress ? ($defaultAddress->address_label . ': ' . $defaultAddress->full_address) : '-',
            'total_orders' => $totalOrders,
            'total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
            'latest_invoice' => $latestOrder ? $latestOrder->invoice_number : '-',
            'latest_invoice_id' => $latestOrder ? $latestOrder->id : null,
            'latest_status' => $latestOrder ? ucfirst($latestOrder->order_status instanceof \App\Enums\OrderStatus ? $latestOrder->order_status->value : (string)$latestOrder->order_status) : '-',
            'latest_tracking' => $latestOrder ? ($latestOrder->tracking_status_label ?? 'Menunggu Konfirmasi') : '-',
            'latest_total' => $latestOrder ? 'Rp ' . number_format($latestOrder->total_price, 0, ',', '.') : '-',
        ];

        return response()->json(['messages' => $messages, 'crm' => $crmData]);
    }

    /**
     * Admin: Toggle Mode AI vs Mode Manual CS untuk Chat Room
     */
    public function toggleAiMode(ChatRoom $room, Request $request): JsonResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $enable = $request->boolean('enable', true);

        if ($enable) {
            \Illuminate\Support\Facades\Cache::forget('chat_room_ai_paused_' . $room->id);
            \Illuminate\Support\Facades\Cache::forget('chat_room_manual_' . $room->id);
        } else {
            \Illuminate\Support\Facades\Cache::forever('chat_room_ai_paused_' . $room->id, true);
            \Illuminate\Support\Facades\Cache::forever('chat_room_manual_' . $room->id, true);
        }

        return response()->json([
            'success' => true,
            'ai_enabled' => $enable,
            'message' => $enable ? 'AI Bot Aktif untuk room ini.' : 'Mode Admin CS Manual Aktif.'
        ]);
    }

    /**
     * Owner: Pantau Seluruh Room & Statistik CRM
     */
    public function ownerIndex()
    {
        if (Auth::user()->role !== 'owner') {
            abort(403);
        }

        $totalChat = ChatRoom::count();
        $activeChat = ChatRoom::where('status', 'open')->count();
        $closedChat = ChatRoom::where('status', 'closed')->count();
        
        // Customer Aktif: customer yang pernah mengirim pesan dalam 7 hari terakhir
        $activeCustomers = ChatRoom::whereHas('messages', function ($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        })->count();

        $rooms = ChatRoom::with(['customer', 'lastMessage'])->latest('updated_at')->get();

        return view('chat.owner', compact('totalChat', 'activeChat', 'closedChat', 'activeCustomers', 'rooms'));
    }

    /**
     * Global Badge Notifikasi Unread (AJAX)
     */
    public function unreadBadge(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread' => 0]);
        }

        if ($user->role === 'admin') {
            $count = ChatRoom::sum('unread_admin');
            return response()->json(['unread' => $count]);
        }

        if ($user->role === 'customer' || $user->role === null || $user->role === '') {
            $room = ChatRoom::where('customer_id', $user->id)->first();
            return response()->json(['unread' => $room ? $room->unread_customer : 0]);
        }

        return response()->json(['unread' => 0]);
    }

    /**
     * Resolves admin avatar URL based on the priorities:
     * 1. Admin user avatar (relative path or external url)
     * 2. Company logo / web logo from settings
     * 3. Fallback ui-avatars placeholder URL
     */
    public static function resolveAdminAvatar(): string
    {
        $adminUser = User::where('role', 'admin')->first();
        
        // 1. jika admin memiliki foto profil
        if ($adminUser && $adminUser->avatar) {
            if (str_starts_with($adminUser->avatar, 'http://') || str_starts_with($adminUser->avatar, 'https://')) {
                return $adminUser->avatar;
            }
            return asset('storage/' . $adminUser->avatar);
        }

        // 2. jika tidak memiliki foto profil, gunakan logo toko
        $companyLogo = \App\Models\Setting::getVal('company_logo') ?: \App\Models\Setting::getVal('web_logo');
        if ($companyLogo) {
            if (str_starts_with($companyLogo, 'http://') || str_starts_with($companyLogo, 'https://')) {
                return $companyLogo;
            }
            // Check if it already has storage prefix
            if (str_starts_with($companyLogo, '/storage/') || str_starts_with($companyLogo, 'storage/')) {
                return asset($companyLogo);
            }
            return asset('storage/' . $companyLogo);
        }

        // 3. jika logo toko juga kosong, gunakan avatar default
        return 'https://ui-avatars.com/api/?name=Admin+Fadilah&color=ea580c&background=ffedd5&rounded=true';
    }
}
