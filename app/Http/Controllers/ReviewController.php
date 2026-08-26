<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Menyimpan ulasan dari Customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $userId = Auth::id();

        // Verifikasi kepemilikan order dan statusnya
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->order_status !== \App\Enums\OrderStatus::SELESAI) {
            return redirect()->back()->with('error', 'Anda hanya dapat memberikan ulasan pada pesanan yang sudah Selesai.');
        }

        // Pastikan order item tersebut ada
        $hasProduct = $order->orderDetails()->where('product_id', $request->product_id)->exists();
        if (!$hasProduct) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan di dalam pesanan ini.');
        }

        // Cegah ulasan ganda untuk item di pesanan yang sama
        $exist = Review::where('order_id', $order->id)
            ->where('product_id', $request->product_id)
            ->where('user_id', $userId)
            ->exists();

        if ($exist) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini pada pesanan tersebut.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reviews', 'public');
        }

        Review::create([
            'order_id' => $order->id,
            'product_id' => $request->product_id,
            'user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'photo' => $photoPath ? '/storage/' . $photoPath : null,
        ]);

        // Simpan log aktivitas
        ActivityLog::create([
            'user_id' => $userId,
            'activity' => 'Kirim Ulasan',
            'description' => "Customer mengirim ulasan bintang {$request->rating} untuk produk ID: {$request->product_id} pada order #{$order->invoice_number}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    /**
     * Dashboard Ulasan di halaman Admin.
     */
    public function indexAdmin()
    {
        $reviews = Review::with(['product', 'user', 'order'])
            ->latest()
            ->get();

        // Statistik rating
        $totalReviews = Review::count();
        $averageRating = round(Review::avg('rating') ?? 0, 1);
        $ratingStats = [
            5 => Review::where('rating', 5)->count(),
            4 => Review::where('rating', 4)->count(),
            3 => Review::where('rating', 3)->count(),
            2 => Review::where('rating', 2)->count(),
            1 => Review::where('rating', 1)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'averageRating', 'ratingStats'));
    }

    /**
     * Admin membalas ulasan.
     */
    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $review->update([
            'reply' => $request->reply,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Balas Ulasan',
            'description' => "Admin membalas ulasan ID: {$review->id} dari customer {$review->user->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Balasan ulasan berhasil disimpan!');
    }

    /**
     * Sembunyikan atau tampilkan ulasan (Toggle Visibility).
     */
    public function toggleVisibility(Request $request, Review $review)
    {
        $review->update([
            'is_visible' => !$review->is_visible,
        ]);

        $status = $review->is_visible ? 'ditampilkan' : 'disembunyikan';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Ubah Visibilitas Ulasan',
            'description' => "Admin mengubah visibilitas ulasan ID: {$review->id} menjadi {$status}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', "Ulasan berhasil {$status}!");
    }

    /**
     * Hapus ulasan oleh Admin.
     */
    public function destroy(Review $review)
    {
        $id = $review->id;
        $review->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Hapus Ulasan',
            'description' => "Admin menghapus ulasan ID: {$id}.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
