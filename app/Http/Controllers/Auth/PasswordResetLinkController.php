<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Mengirimkan tautan reset password secara sinkron
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (Throwable $e) {
            // Catat kegagalan koneksi/otentikasi SMTP secara detail ke log lokal
            Log::error('[SMTP-Mail] Gagal mengirimkan email reset password.', [
                'email'     => $request->email,
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'host'      => config('mail.mailers.smtp.host'),
                'port'      => config('mail.mailers.smtp.port'),
                'trace'     => $e->getTraceAsString(),
            ]);

            // Pesan informatif untuk mode local agar developer langsung tahu masalahnya
            if (app()->environment('local')) {
                $debugMsg = 'Gagal mengirimkan email melalui SMTP. Pastikan kredensial SMTP Gmail Anda di berkas .env sudah dikonfigurasi dengan benar. [Error: (' . get_class($e) . ') ' . $e->getMessage() . ']';
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => $debugMsg]);
            }

            // Pesan aman untuk mode production
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Gagal mengirimkan email reset password. Silakan coba beberapa saat lagi atau hubungi administrator.']);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
