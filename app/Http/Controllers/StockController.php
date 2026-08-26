<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        // Mengambil semua produk beserta kategorinya untuk dikelola stoknya
        $products = Product::with(['category', 'stockLogs.user'])->latest()->get();
        return view('stok.index', compact('products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jumlah_stok' => 'required|integer|min:0',
            'aksi' => 'required|in:tambah,atur',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($id);
        $oldStock = $product->stock;

        if ($request->aksi === 'tambah') {
            $product->increment('stock', $request->jumlah_stok);
            $newStock = $product->stock;
            $diff = $request->jumlah_stok;
            $type = 'in';
            $pesan = "Stok produk {$product->product_name} berhasil ditambah sebanyak {$request->jumlah_stok}!";
        } else {
            $product->update(['stock' => $request->jumlah_stok]);
            $newStock = $request->jumlah_stok;
            $diff = abs($newStock - $oldStock);
            $type = $newStock >= $oldStock ? 'in' : 'out';
            $pesan = "Stok produk {$product->product_name} berhasil diatur menjadi {$request->jumlah_stok}!";
        }

        // Catat mutasi stok
        StockLog::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $diff,
            'description' => $request->keterangan ?: ($type === 'in' ? "Penambahan stok oleh admin." : "Pengurangan stok oleh admin."),
            'user_id' => Auth::id(),
        ]);

        // Kirim notifikasi jika stok kembali tersedia untuk produk terfavorit


        // Catat aktivitas admin
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Update Stok',
            'description' => "Admin memperbarui stok '{$product->product_name}' dari {$oldStock} menjadi {$newStock} (aksi: {$request->aksi}).",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('stok.index')->with('success', $pesan);
    }
}