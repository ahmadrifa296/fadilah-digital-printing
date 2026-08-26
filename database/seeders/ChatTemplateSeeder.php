<?php

namespace Database\Seeders;

use App\Models\ChatTemplate;
use Illuminate\Database\Seeder;

class ChatTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'title' => 'Halo Kak',
                'message' => 'Halo Kak 👋',
            ],
            [
                'title' => 'Terima kasih',
                'message' => 'Terima kasih sudah menghubungi Fadilah Printing.',
            ],
            [
                'title' => 'Pesanan diproses',
                'message' => 'Pesanan Kakak sedang diproses.',
            ],
            [
                'title' => 'Upload desain',
                'message' => 'Silakan upload file desain.',
            ],
            [
                'title' => 'Ready Stock',
                'message' => 'Produk Ready Stock.',
            ],
            [
                'title' => 'Custom desain',
                'message' => 'Produk Custom membutuhkan file desain.',
            ],
        ];

        foreach ($templates as $tmpl) {
            ChatTemplate::updateOrCreate(
                ['title' => $tmpl['title']],
                ['message' => $tmpl['message']]
            );
        }
    }
}
