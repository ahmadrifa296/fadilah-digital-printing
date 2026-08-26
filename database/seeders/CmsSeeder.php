<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\CmsBanner;
use App\Models\CmsTestimonial;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Settings
        Setting::setVal('web_name', 'Fadilah Digital Printing', 'Nama Website/Toko');
        Setting::setVal('web_logo', '', 'Logo Website');
        Setting::setVal('web_phone', '081234567890', 'Nomor Telepon Kontak');
        Setting::setVal('web_email', 'info@fadilahprinting.com', 'Alamat Email Toko');
        Setting::setVal('web_address', 'Jl. Percetakan Modern No. 45, Jakarta Selatan', 'Alamat Fisik Toko');
        Setting::setVal('web_about', 'Fadilah Digital Printing adalah penyedia jasa percetakan digital profesional dengan mesin berteknologi tinggi untuk menghasilkan spanduk, brosur, stempel, plakat, dan undangan berkualitas premium.', 'Tentang Kami');
        Setting::setVal('web_faq', json_encode([
            ['q' => 'Bagaimana cara memesan?', 'a' => 'Pilih produk di katalog, tambahkan ke keranjang, unggah file desain Anda saat checkout, lalu lakukan pembayaran via Midtrans Snap.'],
            ['q' => 'Format file desain apa saja yang didukung?', 'a' => 'Kami mendukung format AI, CDR, PSD, PDF, JPG, dan PNG dengan resolusi minimal 300 DPI untuk hasil cetak terbaik.'],
            ['q' => 'Berapa lama estimasi waktu pengerjaan?', 'a' => 'Waktu pengerjaan standar adalah 1-3 hari kerja tergantung jenis produk dan volume antrian.'],
            ['q' => 'Apakah bisa dikirim ke luar kota?', 'a' => 'Ya, kami mengirim ke seluruh Indonesia menggunakan jasa kurir tepercaya.'],
        ]), 'Pertanyaan yang Sering Diajukan (FAQ)');
        
        Setting::setVal('seo_title', 'Fadilah Digital Printing - Jasa Cetak Spanduk, Brosur & Plakat Premium', 'Meta Title Default');
        Setting::setVal('seo_description', 'Pesan spanduk, x-banner, stempel otomatis, brosur art paper secara online praktis dan cepat dengan pembayaran Midtrans Snap aman.', 'Meta Description Default');
        Setting::setVal('seo_keywords', 'percetakan digital, cetak spanduk online, stempel otomatis, cetak brosur jakarta, digital printing murah', 'Meta Keywords Default');

        // Warehouse (Gudang Asal) Biteship Settings Defaults
        Setting::setVal('warehouse_name', 'Gudang Utama Fadilah Printing', 'Nama Gudang Asal Pengiriman');
        Setting::setVal('warehouse_address', 'Jl. Percetakan Modern No. 45, Kebayoran Baru', 'Alamat Fisik Gudang');
        Setting::setVal('warehouse_province', 'DKI Jakarta', 'Provinsi Gudang');
        Setting::setVal('warehouse_city', 'Jakarta Selatan', 'Kabupaten/Kota Gudang');
        Setting::setVal('warehouse_district', 'Kebayoran Baru', 'Kecamatan Gudang');
        Setting::setVal('warehouse_subdistrict', 'Gandaria Utara', 'Kelurahan/Desa Gudang');
        Setting::setVal('warehouse_postal_code', '12140', 'Kode Pos Gudang');
        Setting::setVal('warehouse_latitude', '-6.2615', 'Latitude Koordinat Gudang');
        Setting::setVal('warehouse_longitude', '106.7995', 'Longitude Koordinat Gudang');
        Setting::setVal('warehouse_biteship_origin_id', '62a84aa27c13cb24e64f7b60', 'Origin ID Biteship Gudang');

        // 2. Seed CmsBanners (Slider)
        CmsBanner::create([
            'title' => 'Cetak Spanduk & Banner Kilat',
            'image' => 'https://images.unsplash.com/photo-1561070791-26c113006238?auto=format&fit=crop&q=80&w=1200&h=450',
            'link' => '#katalog',
            'is_active' => true
        ]);
        CmsBanner::create([
            'title' => 'Stempel & Plakat Custom Premium',
            'image' => 'https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=1200&h=450',
            'link' => '#katalog',
            'is_active' => true
        ]);

        // 3. Seed Testimonials
        CmsTestimonial::create([
            'customer_name' => 'Budi Santoso',
            'rating' => 5,
            'comment' => 'Hasil cetakan Spanduk Flexi sangat tajam dan warnanya keluar sesuai desain. Proses pengerjaan sangat cepat!',
            'photo' => null,
            'is_active' => true
        ]);
        CmsTestimonial::create([
            'customer_name' => 'Siti Aminah',
            'rating' => 5,
            'comment' => 'Stempel Flash-nya sangat praktis, tintanya awet dan warnanya cerah. Sangat direkomendasikan untuk kantor.',
            'photo' => null,
            'is_active' => true
        ]);

        // 4. Seed Product Variants
        $products = Product::all();
        foreach ($products as $product) {
            if (str_contains(strtolower($product->product_name), 'spanduk')) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'bahan',
                    'variant_name' => 'Flexi Standard 280gr',
                    'price_modifier' => 0,
                    'stock' => 1000
                ]);
                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'bahan',
                    'variant_name' => 'Flexi Korchin 380gr',
                    'price_modifier' => 15000,
                    'stock' => 500
                ]);
                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'finishing',
                    'variant_name' => 'Mata Ayam (Lubang Ring)',
                    'price_modifier' => 0,
                    'stock' => 1000
                ]);
                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'finishing',
                    'variant_name' => 'Lebihan Bahan (Polos)',
                    'price_modifier' => 0,
                    'stock' => 1000
                ]);
            }
        }
    }
}
