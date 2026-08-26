<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $user->notify(new \App\Notifications\AppNotification(
            'Registrasi Berhasil',
            "Selamat datang {$user->name}! Registrasi akun Anda di Fadilah Printing berhasil.",
            'user-check',
            'green',
            route('dashboard'),
            'akun'
        ));

        // Notify admins/owners of the new customer registration
        $admins = User::whereIn('role', ['admin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AppNotification(
                'Pelanggan Baru Terdaftar',
                "Pelanggan baru '{$user->name}' ({$user->email}) telah terdaftar.",
                'user-plus',
                'blue',
                '#',
                'akun'
            ));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
