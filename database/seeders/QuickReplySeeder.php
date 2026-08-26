<?php

namespace Database\Seeders;

use App\Models\QuickReply;
use Illuminate\Database\Seeder;

class QuickReplySeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'shortcut' => 'halo',
                'reply_text' => 'Halo Kak, selamat datang di Fadilah Digital Printing 👋 Ada yang bisa kami bantu?',
            ],
            [
                'shortcut' => 'proses',
                'reply_text' => 'Mohon maaf, pesanan Kakak sedang diproses dalam antrean cetak. Harap tunggu ya.',
            ],
            [
                'shortcut' => 'kirim',
                'reply_text' => 'Pesanan Kakak sudah selesai dicetak dan dalam proses pengiriman.',
            ],
            [
                'shortcut' => 'desain',
                'reply_text' => 'Silakan unggah berkas desain Kakak melalui tombol upload di chat ini (support PDF, PNG, JPG, ZIP).',
            ],
            [
                'shortcut' => 'terima_kasih',
                'reply_text' => 'Terima kasih banyak telah mempercayakan cetakan Kakak di Fadilah Digital Printing! 😊',
            ],
        ];

        foreach ($templates as $tmpl) {
            QuickReply::updateOrCreate(
                ['shortcut' => $tmpl['shortcut']],
                ['reply_text' => $tmpl['reply_text']]
            );
        }
    }
}
