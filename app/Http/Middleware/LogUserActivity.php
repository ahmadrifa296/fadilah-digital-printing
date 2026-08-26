<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya catat log jika user login
        if (Auth::check()) {
            $user = Auth::user();
            $method = $request->method();
            $path = $request->path();

            // Kita filter agar hanya mencatat request write (POST, PUT, PATCH, DELETE)
            // agar log tidak terlalu membengkak dengan halaman GET biasa.
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                
                // Jangan catat log jika itu route login, logout, atau register untuk mencegah duplikasi
                if (str_contains($path, 'login') || str_contains($path, 'logout') || str_contains($path, 'register')) {
                    return $response;
                }

                // Terjemahkan path ke deskripsi ramah
                $activity = "Aksi {$method}";
                $description = "User melakukan request {$method} pada /{$path}";

                if (str_contains($path, 'cart')) {
                    $activity = "Update Keranjang";
                    $description = "User memodifikasi item di keranjang belanja.";
                } elseif (str_contains($path, 'checkout')) {
                    $activity = "Proses Checkout";
                    $description = "User memicu pembuatan pesanan baru (checkout).";
                } elseif (str_contains($path, 'profile')) {
                    $activity = "Update Profil";
                    $description = "User mengubah informasi detail akun profil.";
                } elseif (str_contains($path, 'produk')) {
                    $activity = "Master Produk";
                    $description = "User/Admin memodifikasi data master produk.";
                } elseif (str_contains($path, 'kategori')) {
                    $activity = "Master Kategori";
                    $description = "User/Admin memodifikasi data master kategori.";
                } elseif (str_contains($path, 'pesanan')) {
                    $activity = "Update Pesanan";
                    $description = "Admin/User memperbarui status/data pesanan.";
                } elseif (str_contains($path, 'stok')) {
                    $activity = "Manajemen Stok";
                    $description = "Admin memperbarui data kuantitas stok produk.";
                }

                // Simpan log ke DB jika belum dibuat manual oleh controller
                // Untuk mencegah double log, kita bisa skip jika deskripsi log sudah dihandle
                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => $activity,
                    'description' => $description,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        return $response;
    }
}
