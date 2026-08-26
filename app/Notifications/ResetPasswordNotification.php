<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    /**
     * Token reset password.
     *
     * @var string
     */
    public $token;

    /**
     * Buat instance notifikasi baru.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Tentukan saluran pengiriman notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Bangun pesan email untuk notifikasi.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Bangun tautan reset password lengkap dengan email
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        // Ambil masa kedaluwarsa token dari config auth (menit)
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('[' . config('app.name', 'Fadilah Printing') . '] Atur Ulang Kata Sandi Akun Anda')
            ->view('emails.reset-password', [
                'url'    => $url,
                'user'   => $notifiable,
                'expire' => $expire,
            ]);
    }
}
