<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case DIPROSES = 'diproses';
    case SEDANG_DICETAK = 'sedang_dicetak';
    case SIAP_DIKEMAS = 'siap_dikemas';
    case DIKEMAS = 'dikemas';
    case DIKIRIM = 'dikirim';
    case SELESAI = 'selesai';
    case DIBATALKAN = 'dibatalkan';
    case SIAP_DIAMBIL = 'siap_diambil';

    /**
     * Get label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING        => 'Menunggu Pembayaran',
            self::PAID, self::DIPROSES => 'Diproses',
            self::SEDANG_DICETAK => 'Sedang Dicetak',
            self::SIAP_DIKEMAS   => 'Siap Dikemas',
            self::DIKEMAS        => 'Dikemas',
            self::DIKIRIM        => 'Dikirim',
            self::SELESAI        => 'Selesai',
            self::DIBATALKAN     => 'Dibatalkan',
            self::SIAP_DIAMBIL   => 'Siap Diambil',
        };
    }

    /**
     * Get CSS badge class for tailwind styling.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING        => 'bg-yellow-100 text-yellow-800',
            self::PAID, self::DIPROSES => 'bg-orange-105 text-orange-850 border-orange-200',
            self::SEDANG_DICETAK => 'bg-blue-100 text-blue-800',
            self::SIAP_DIKEMAS   => 'bg-purple-100 text-purple-800',
            self::DIKEMAS        => 'bg-indigo-100 text-indigo-800',
            self::DIKIRIM        => 'bg-cyan-100 text-cyan-800',
            self::SELESAI        => 'bg-green-100 text-green-800',
            self::DIBATALKAN     => 'bg-red-100 text-red-800',
            self::SIAP_DIAMBIL   => 'bg-purple-100 text-purple-800',
        };
    }
}
