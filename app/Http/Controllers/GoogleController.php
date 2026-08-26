<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redirect pengguna ke halaman autentikasi Google.
     *
     * Catatan: Verifikasi SSL ditegakkan penuh demi keamanan standar production.
     */
    public function redirect(): RedirectResponse
    {
        Log::info('[GoogleOAuth] Menginisiasi redirect ke Google.');

        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            Log::error('[GoogleOAuth] Gagal menginisiasi redirect ke Google.', [
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'trace'           => $e->getTraceAsString(),
            ]);

            $errorMsg = $this->formatDebugError('Gagal menginisiasi redirect ke Google.', $e);
            return redirect()->route('login')->with('error', $errorMsg);
        }
    }

    /**
     * Tangani callback dari Google setelah autentikasi berhasil.
     *
     * Flow:
     *  1. Ambil user dari Google (gagal → log + flash + redirect login).
     *  2. Validasi email tersedia.
     *  3. Cari user berdasarkan google_id → login langsung.
     *  4. Cari berdasarkan email → update google_id & avatar jika kosong.
     *  5. Buat user baru jika tidak ditemukan (role=customer, password random).
     *  6. Login + redirect ke dashboard (DashboardController handle role-based routing).
     */
    public function callback(): RedirectResponse
    {
        Log::info('[GoogleOAuth] Menerima callback dari Google.');

        // ─────────────────────────────────────────────────────────────────
        // 1. Ambil data pengguna dari Google
        // ─────────────────────────────────────────────────────────────────
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Log error lengkap ke storage/logs/laravel.log untuk keperluan audit
            Log::error('[GoogleOAuth] Gagal mengambil data pengguna dari Google.', [
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'trace'           => $e->getTraceAsString(),
            ]);

            // Tentukan pesan user-friendly atau debug-friendly
            $userMessage = $this->resolveOAuthError($e);

            return redirect()->route('login')->with('error', $userMessage);
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. Validasi dasar — email wajib ada
        // ─────────────────────────────────────────────────────────────────
        if (empty($googleUser->getEmail())) {
            Log::warning('[GoogleOAuth] Akun Google tidak memiliki email.', [
                'google_id' => $googleUser->getId(),
                'name'      => $googleUser->getName(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Akun Google Anda tidak memiliki alamat email yang valid. Silakan gunakan akun Google lain.');
        }

        // ─────────────────────────────────────────────────────────────────
        // 3. Cari atau buat user di database
        // ─────────────────────────────────────────────────────────────────
        try {
            $user = $this->findOrCreateUser($googleUser);
        } catch (Throwable $e) {
            Log::error('[GoogleOAuth] Gagal menyimpan/menemukan user di database.', [
                'email'           => $googleUser->getEmail(),
                'google_id'       => $googleUser->getId(),
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'trace'           => $e->getTraceAsString(),
            ]);

            $errorMsg = $this->formatDebugError('Gagal terhubung ke Google. Terjadi kesalahan pada database.', $e);
            return redirect()->route('login')->with('error', $errorMsg);
        }

        // ─────────────────────────────────────────────────────────────────
        // 4. Login user dan redirect ke dashboard
        // ─────────────────────────────────────────────────────────────────
        Auth::login($user, remember: true);

        $user->notify(new \App\Notifications\AppNotification(
            'Login Google Berhasil',
            "Anda berhasil masuk menggunakan akun Google.",
            'shield-check',
            'blue',
            route('dashboard'),
            'akun'
        ));

        Log::info('[GoogleOAuth] Login berhasil.', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'role'    => $user->role,
        ]);

        // DashboardController@index menangani routing berdasarkan role:
        // role=admin  → tampilkan dashboard admin
        // role=customer → tampilkan dashboard customer
        return redirect()->intended(route('dashboard'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Cari user existing atau buat user baru dari data Google.
     *
     * Aturan:
     * - google_id cocok → login langsung (user sudah pernah OAuth)
     * - email cocok     → hubungkan google_id (jangan ubah role/password)
     * - tidak ada       → buat user baru dengan role=customer
     */
    private function findOrCreateUser(object $googleUser): User
    {
        // Prioritas 1: cari via google_id (paling deterministik)
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Update avatar jika kosong (foto profil bisa berubah)
            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }

            return $user;
        }

        // Prioritas 2: cari via email (akun sudah ada via email/password)
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Hubungkan google_id — JANGAN ubah role atau password yang sudah ada
            $updateData = [];

            if (empty($user->google_id)) {
                $updateData['google_id'] = $googleUser->getId();
            }
            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $updateData['avatar'] = $googleUser->getAvatar();
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            Log::info('[GoogleOAuth] Akun lama dihubungkan ke Google.', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => $user->role,
            ]);

            return $user;
        }

        // Prioritas 3: buat akun baru
        $user = User::create([
            'name'              => $googleUser->getName() ?: 'Pengguna Google',
            'email'             => $googleUser->getEmail(),
            'password'          => Str::random(32), // Password random — tidak bisa login via form
            'role'              => 'customer',
            'google_id'         => $googleUser->getId(),
            'avatar'            => $googleUser->getAvatar(),
            'email_verified_at' => now(), // Google sudah memverifikasi email
        ]);

        // Notify admins/owners of the new customer registration via Google
        $admins = User::whereIn('role', ['admin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AppNotification(
                'Pelanggan Baru Terdaftar',
                "Pelanggan baru '{$user->name}' ({$user->email}) terdaftar melalui Google.",
                'user-plus',
                'blue',
                '#',
                'akun'
            ));
        }

        Log::info('[GoogleOAuth] Akun baru dibuat via Google.', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        return $user;
    }

    /**
     * Terjemahkan exception OAuth menjadi pesan yang tepat untuk user.
     * Di mode local, tambahkan detail error ke log.
     * Di mode production, kembalikan pesan generik yang aman.
     */
    private function resolveOAuthError(Throwable $e): string
    {
        $message = $e->getMessage();

        // Klasifikasikan error berdasarkan pesan/jenis
        if (str_contains($message, 'redirect_uri_mismatch') || str_contains($message, 'redirect URI')) {
            return $this->formatDebugError('Konfigurasi Google OAuth belum sesuai (redirect URI mismatch). Hubungi administrator.', $e);
        }

        if (str_contains($message, 'invalid_client')) {
            return $this->formatDebugError('Client ID atau Client Secret Google tidak valid. Hubungi administrator.', $e);
        }

        if (str_contains($message, 'invalid_grant') || str_contains($message, 'Token')) {
            return $this->formatDebugError('Sesi Google habis. Silakan coba login dengan Google sekali lagi.', $e);
        }

        if (str_contains($message, 'access_denied') || str_contains($message, 'cancelled')) {
            return $this->formatDebugError('Login dengan Google dibatalkan. Silakan coba lagi.', $e);
        }

        if (str_contains($message, 'invalid_state') || str_contains($message, 'State')) {
            return $this->formatDebugError('Sesi login tidak valid (state mismatch). Silakan refresh halaman dan coba lagi.', $e);
        }

        // Pesan default yang aman untuk production
        return $this->formatDebugError('Gagal terhubung ke Google. Silakan coba beberapa saat lagi atau hubungi administrator.', $e);
    }

    /**
     * Format pesan error dengan detail debugging jika di environment local.
     */
    private function formatDebugError(string $defaultMessage, Throwable $e): string
    {
        if (app()->environment('local')) {
            return $defaultMessage . ' [Detail Error: (' . get_class($e) . ') ' . $e->getMessage() . ' di file ' . basename($e->getFile()) . ':' . $e->getLine() . ']';
        }

        return $defaultMessage;
    }
}
