<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. PANGGIL USER SEEDER YANG SUDAH KAMU BUAT
        $this->call([
            UserSeeder::class,
            CmsSeeder::class,
            ChatTemplateSeeder::class,
        ]);

        // 2. MEMBUAT KATEGORI BARANG DUMMY
        $katBanner = Category::create(['category_name' => 'Spanduk & Banner']);
        $katStempel = Category::create(['category_name' => 'Stempel & Plakat']);
        $katKertas = Category::create(['category_name' => 'Brosur & Undangan']);

        // 3. MEMBUAT PRODUK DUMMY
        Product::create([
            'category_id' => $katBanner->id,
            'product_name' => 'Spanduk Flexi 280gr',
            'price' => 25000,
            'stock' => 100,
            'description' => 'Harga dihitung per meter persegi. Bahan standar awet untuk outdoor.',
        ]);

        Product::create([
            'category_id' => $katBanner->id,
            'product_name' => 'X-Banner + Tiang (60x160cm)',
            'price' => 85000,
            'stock' => 50,
            'description' => 'Sudah termasuk kerangka tiang X dan cetakan bahan Albatros.',
        ]);

        Product::create([
            'category_id' => $katStempel->id,
            'product_name' => 'Stempel Flash (Otomatis)',
            'price' => 65000,
            'stock' => 200,
            'description' => 'Stempel tanpa bak tinta. Bisa pilih 1 sampai 3 warna.',
        ]);

        Product::create([
            'category_id' => $katKertas->id,
            'product_name' => 'Brosur A4 Lipat 3 (Art Paper)',
            'price' => 1500,
            'stock' => 5000,
            'description' => 'Minimal pemesanan 500 lembar. Cetak full color 2 sisi.',
        ]);
    }
}