<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // Tambahan untuk aplikasi kamu
        'phone_number',  // Tambahan untuk aplikasi kamu
        'google_id',     // Google OAuth
        'avatar',        // Google Profile Avatar URL / Predefined Avatar
        'username',
        'gender',
        'birth_date',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /**
     * Relasi: Satu User (Pelanggan) bisa punya banyak Order (Pesanan).
     * Tipe: One-to-Many (HasMany)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relasi: Satu User bisa memiliki banyak item di keranjang belanja.
     * Tipe: One-to-Many (HasMany)
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }



    /**
     * Relasi: Catatan aktivitas sistem (Activity Log).
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Relasi: Ulasan ulasan dari user ini.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relasi ke Alamat Pengiriman
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Override method notifikasi reset password bawaan.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get user avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }

        // Fallback: Initial default avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=ea580c&background=ffedd5&rounded=true';
    }
}