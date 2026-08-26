<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Customer: Tampilkan halaman "Notifikasi Saya" dengan filter.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'semua');

        $query = $user->notifications();

        // Terapkan filter kategori
        if (in_array($filter, ['pesanan', 'pembayaran', 'produk', 'promo', 'chat', 'akun', 'sistem'])) {
            $query->where('data->category', $filter);
        } elseif ($filter === 'belum_dibaca') {
            $query->whereNull('read_at');
        } elseif ($filter === 'sudah_dibaca') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(10)->withQueryString();

        return view('notifications.index', compact('notifications', 'filter'));
    }

    /**
     * Customer: Klik notifikasi (tandai dibaca & redirect ke URL tujuan).
     */
    public function click($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? '#';
        if (empty($url) || $url === '#') {
            return redirect()->back();
        }

        return redirect($url);
    }

    /**
     * Customer: Tandai semua notifikasi sebagai telah dibaca.
     */
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Semua notifikasi berhasil ditandai telah dibaca.');
    }

    /**
     * Customer/Admin: Dapatkan jumlah notifikasi belum dibaca (AJAX polling).
     */
    public function unreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Customer/Admin: Dapatkan daftar notifikasi terbaru untuk dropdown navbar (AJAX polling).
     */
    public function navbarList()
    {
        if (!Auth::check()) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Notifikasi',
                    'content' => $n->data['content'] ?? '',
                    'icon' => $n->data['icon'] ?? 'bell',
                    'color' => $n->data['color'] ?? 'orange',
                    'url' => route('notifications.click', $n->id),
                    'is_read' => !is_null($n->read_at),
                    'time' => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Admin Dashboard: Tampilkan halaman manajemen notifikasi.
     */
    public function adminIndex(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403);
        }

        // Ambil daftar pelanggan untuk pilihan target notifikasi
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        // Ambil seluruh notifikasi di sistem (eager load notifiable untuk relasi user)
        $notifications = DatabaseNotification::with('notifiable')
            ->latest()
            ->paginate(15);

        return view('admin.notifications.index', compact('customers', 'notifications'));
    }

    /**
     * Admin: Kirim notifikasi kustom / broadcast ke pelanggan.
     */
    public function adminSend(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403);
        }

        $request->validate([
            'target_type' => 'required|in:all,single',
            'user_id' => 'required_if:target_type,single|nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'category' => 'required|in:pesanan,pembayaran,produk,promo,chat,akun,sistem',
            'icon' => 'required|string|in:bell,shopping-bag,credit-card,tag,percent,chat,user,shield,info,alert-triangle',
            'color' => 'required|string|in:orange,blue,green,red,yellow,purple,pink,cyan,slate',
            'url' => 'nullable|string|max:255',
        ]);

        $url = $request->url ?: '#';
        $notification = new AppNotification(
            $request->title,
            $request->content,
            $request->icon,
            $request->color,
            $url,
            $request->category
        );

        if ($request->target_type === 'all') {
            $customers = User::where('role', 'customer')->get();
            foreach ($customers as $customer) {
                $customer->notify($notification);
            }
            $msg = 'Notifikasi broadcast berhasil dikirim ke seluruh pelanggan!';
        } else {
            $customer = User::findOrFail($request->user_id);
            $customer->notify($notification);
            $msg = "Notifikasi berhasil dikirim ke pelanggan '{$customer->name}'!";
        }

        // Catat aktivitas admin
        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Kirim Notifikasi',
            'description' => "Admin mengirim notifikasi: '{$request->title}' (target: {$request->target_type})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Admin: Hapus notifikasi tertentu dari database.
     */
    public function adminDestroy($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403);
        }

        $notification = DatabaseNotification::findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
