<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $request->user()->notify(new \App\Notifications\AppNotification(
            'Kata Sandi Diubah',
            'Kata sandi Anda berhasil diperbarui. Keamanan akun Anda terjaga.',
            'key',
            'amber',
            route('profile.edit'),
            'akun'
        ));

        return back()->with('status', 'password-updated');
    }
}
