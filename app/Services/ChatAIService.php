<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Setting;
use App\Models\CmsBanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChatAIService
{
   /**
    * Helper to read dynamic setting from database or store default if missing.
    */
   private function getSettingOrStore(string $key, string $default, string $description = ''): string
   {
       $setting = Setting::where('key', $key)->first();
       if (!$setting) {
           $setting = Setting::create([
               'key' => $key,
               'value' => $default,
               'description' => $description
           ]);
       }
       return $setting->value;
   }

   /**
    * Cari respon otomatis berdasarkan pesan dari customer.
    *
    * @param string $message
    * @param \App\Models\User|null $user
    * @return string|null
    */
   public function getReply(string $message, $user = null): ?string
   {
       $originalMessage = trim($message);
       $lower = trim(strtolower($message));
       // Strip punctuation/symbols for matching but keep original for display
       $message = preg_replace('/[^\w\s\-]/u', '', $lower);
       $message = trim(preg_replace('/\s+/', ' ', $message));

       // 0. Salam & Greetings — harus dicek paling awal sebelum keyword search
       $greetingWords = ['halo', 'hai', 'hello', 'hallo', 'hay', 'assalamualaikum', 'permisi', 'selamat', 'tes', 'test', 'p', 'q', 'hei'];
       $greetingPhrases = ['halo kak', 'hai kak', 'hallo kak', 'pagi kak', 'siang kak', 'malam kak', 'selamat pagi', 'selamat siang', 'selamat malam', 'hei kak', 'halo min', 'hai min'];
       if (in_array($message, $greetingWords) || Str::contains($message, $greetingPhrases)) {
           return "Halo 👋 Selamat datang di **Fadilah Digital Printing**! Senang bisa melayani Anda 😊\n\nAda yang bisa kami bantu? Anda bisa bertanya tentang:\n- 🖨️ Produk cetak & harga\n- 📦 Status pesanan Anda\n- 🚚 Estimasi pengiriman\n- 💳 Cara order & pembayaran";
       }

       // 0b. Niat beli — sebelum pencarian produk
       if (Str::contains($message, ['mau beli', 'ingin beli', 'pengen beli', 'mau pesan', 'ingin pesan', 'pengen pesan', 'mau order', 'ingin order'])) {
           // Coba ekstrak nama produk setelah kata "beli/pesan/order"
           $afterBuy = preg_replace('/^.*(mau beli|ingin beli|pengen beli|mau pesan|ingin pesan|pengen pesan|mau order|ingin order)\s*/u', '', $message);
           $afterBuy = trim($afterBuy);
           if (strlen($afterBuy) > 2) {
               $products = Product::with(['category', 'variants'])
                   ->where('product_name', 'LIKE', "%{$afterBuy}%")
                   ->take(5)->get();
               if ($products->isNotEmpty()) {
                   if ($products->count() === 1) {
                       return "Senang Anda tertarik! 😊 Berikut detail produk yang Anda maksud:\n\n" . $this->renderDetailedProductCard($products->first());
                   }
                   return "Senang Anda tertarik! 😊 Berikut beberapa produk yang relevan:\n\n" . $this->renderCompactProductList("", $products);
               }
           }
           return "Senang Anda tertarik berbelanja di Fadilah Digital Printing! 😊\n\nSilakan sebutkan nama produk yang ingin Anda pesan, atau kunjungi katalog kami untuk melihat semua produk tersedia.";
       }

       // 0c. Custom Print / Cetak Custom
       if (Str::contains($message, ['print custom', 'cetak custom', 'pesan custom', 'bikin custom', 'custom print', 'cetak spanduk', 'cetak banner', 'cetak brosur', 'cetak stempel', 'mau cetak', 'ingin cetak', 'pengen cetak'])) {
           return $this->getSettingOrStore(
               'faq_print_custom',
               "🎨 **Layanan Cetak Custom Fadilah Printing:**\nKami melayani berbagai kebutuhan cetak custom:\n- Spanduk & Banner (Flexi 280g/340g, Albatros)\n- X-Banner & Roll Up Banner\n- Stempel Otomatis (Flash/Warna)\n- Brosur, Stiker, Kartu Nama, & Plakat\n\nSilakan pilih produk di Katalog Produk kami, atau diskusikan spesifikasi cetak yang Anda inginkan dengan Admin CS!",
               'FAQ Cetak Custom'
           );
       }

       // 1. FAQ Dinamis: Cara Order / Cara Pesan
       if (Str::contains($message, ['cara order', 'cara pesan', 'cara beli', 'gimana cara', 'bagaimana cara'])) {
           return $this->getSettingOrStore(
               'faq_cara_order',
               "**Cara Order di Fadilah Digital Printing:**\n1. Pilih produk di Katalog.\n2. Tentukan ukuran, bahan, finishing, dan opsi kustomisasi.\n3. Klik 'Keranjang' atau 'Beli Sekarang'.\n4. Masuk ke halaman Checkout, isi alamat pengiriman, dan pilih kurir Biteship.\n5. Lakukan pembayaran online aman via Midtrans.",
               'FAQ Cara Order'
           );
       }

       // 2. FAQ Dinamis: Cara Upload Desain
       if (Str::contains($message, ['upload desain', 'cara upload', 'unggah desain', 'kirim desain', 'file desain'])) {
           return $this->getSettingOrStore(
               'faq_upload_desain',
               "**Cara Upload Desain:**\nUnggah file desain Anda langsung pada halaman Checkout di kolom 'Unggah File Desain'. Format yang didukung: JPG, PNG, PDF, AI, PSD, CDR, ZIP, RAR (Maks 50 MB). Jika file terlalu besar, Anda bisa mengirimkan link Google Drive via chat.",
               'FAQ Upload Desain'
           );
       }

       // 3. FAQ Dinamis: Pembayaran / Midtrans
       if (Str::contains($message, ['pembayaran', 'midtrans', 'cara bayar', 'bayar pakai', 'transfer', 'qris', 'virtual account'])) {
           return $this->getSettingOrStore(
               'faq_pembayaran',
               "**Metode Pembayaran (Midtrans):**\nKami menerima pembayaran online aman via Midtrans: Virtual Account (BCA, Mandiri, BNI, BRI), QRIS (GoPay, OVO, ShopeePay), Kartu Kredit/Debit, dan Minimarket (Alfamart, Indomaret).",
               'FAQ Pembayaran'
           );
       }

       // 4. FAQ Dinamis: Ongkir / Tarif Pengiriman
       if (Str::contains($message, ['ongkir', 'ongkos kirim', 'tarif pengiriman', 'biaya kirim', 'biaya pengiriman'])) {
           return $this->getSettingOrStore(
               'faq_ongkir',
               "**Informasi Ongkir:**\nTarif pengiriman dihitung secara otomatis dan realtime oleh Biteship saat checkout berdasarkan berat total produk dan alamat tujuan Anda.",
               'FAQ Ongkos Kirim'
           );
       }

       // 5. FAQ Dinamis: Estimasi / Lama Pengiriman
       if (Str::contains($message, ['estimasi pengiriman', 'lama pengiriman', 'berapa hari', 'estimasi selesai', 'kapan selesai', 'berapa lama'])) {
           return $this->getSettingOrStore(
               'faq_estimasi_pengiriman',
               "**Estimasi Pengerjaan & Pengiriman:**\nWaktu produksi standar adalah 1-3 hari kerja sejak file desain disetujui. Estimasi pengiriman tergantung pada lokasi Anda dan jenis kurir yang dipilih (J&T, JNE, SiCepat, dll.).",
               'FAQ Estimasi Pengiriman'
           );
       }

       // 6. FAQ Dinamis: Jasa Kurir / Ekspedisi
       if (Str::contains($message, ['jasa kurir', 'ekspedisi', 'kurir apa', 'jnt', 'jne', 'sicepat', 'anteraja'])) {
           return $this->getSettingOrStore(
               'faq_jasa_kurir',
               "**Pilihan Jasa Kurir:**\nKami bekerjasama dengan Biteship untuk menyediakan kurir terpercaya seperti J&T, JNE, SiCepat, AnterAja, POS Indonesia, dll.",
               'FAQ Jasa Kurir'
           );
       }

       // 7. FAQ Dinamis: Alamat / Lokasi Toko
       if (Str::contains($message, ['alamat toko', 'lokasi toko', 'toko di mana', 'toko dimana', 'alamat lengkap', 'dimana tokonya', 'dimana toko', 'lokasi'])) {
           $address = $this->getSettingOrStore('web_address', 'Jl. Percetakan Modern No. 45, Kebayoran Baru, Jakarta Selatan', 'Alamat Fisik Toko');
           return "📍 **Alamat Toko Kami:**\n" . $address;
       }

       // 8. FAQ Dinamis: Jam Operasional
       if (Str::contains($message, ['jam operasional', 'jam buka', 'buka jam', 'hari kerja', 'buka sampai', 'jam berapa'])) {
           $hours = $this->getSettingOrStore('web_hours', 'Senin - Sabtu: 08:00 - 17:00 WIB (Minggu dan hari libur nasional tutup)', 'Jam Operasional Toko');
           return "🕒 **Jam Operasional Toko:**\n" . $hours;
       }

       // 9. FAQ Dinamis: Nomor WhatsApp
       if (Str::contains($message, ['whatsapp', 'nomor wa', 'wa toko', 'no wa', 'nomor hp', 'nomor telepon', 'kontak'])) {
           $wa = $this->getSettingOrStore('web_phone', '081234567890', 'Nomor WhatsApp');
           return "📞 **Nomor WhatsApp Resmi:**\n" . $wa;
       }

       // 10. FAQ Dinamis: Email
       if (Str::contains($message, ['email', 'alamat email', 'email toko'])) {
           $email = $this->getSettingOrStore('web_email', 'info@fadilahprinting.com', 'Alamat Email Toko');
           return "✉️ **Email Resmi Kami:**\n" . $email;
       }

       // 11. FAQ Dinamis: Sosial Media
       if (Str::contains($message, ['sosial media', 'instagram', 'facebook', 'sosmed', 'ig toko', 'akun ig'])) {
           return $this->getSettingOrStore(
               'faq_sosial_media',
               "**Sosial Media Kami:**\n- Instagram: @fadilahprinting\n- Facebook: Fadilah Digital Printing",
               'FAQ Sosial Media'
           );
       }

       // 12. FAQ Dinamis: Pembatalan / Cancel
       if (Str::contains($message, ['pembatalan', 'batal', 'cancel', 'batalkan'])) {
           return $this->getSettingOrStore(
               'faq_pembatalan',
               "**Kebijakan Pembatalan:**\nPembatalan order hanya dapat diproses sebelum status pengerjaan pesanan memasuki tahap cetak/produksi. Hubungi admin via chat ini segera.",
               'FAQ Pembatalan Order'
           );
       }

       // 13. FAQ Dinamis: Refund / Pengembalian Dana
       if (Str::contains($message, ['refund', 'pengembalian dana', 'kembali uang', 'uang kembali', 'komplain'])) {
           return $this->getSettingOrStore(
               'faq_refund',
               "**Kebijakan Refund:**\nPengembalian dana diproses jika pesanan dibatalkan sebelum diproduksi, atau terdapat kesalahan produksi dari pihak kami. Harap lampirkan bukti foto/video unboxing paket.",
               'FAQ Refund'
           );
       }

       // 14. FAQ Dinamis: Garansi
       if (Str::contains($message, ['garansi', 'jaminan kualitas', 'kualitas cetak'])) {
           return $this->getSettingOrStore(
               'faq_garansi',
               "**Garansi Kualitas Cetak:**\nKami memberikan garansi kualitas cetak sesuai dengan spesifikasi yang telah disepakati. Jika terdapat cacat produksi, kami akan melakukan cetak ulang atau pengembalian dana.",
               'FAQ Garansi'
           );
       }

       // 15. Pengambilan di Toko
       if (Str::contains($message, ['pengambilan di toko', 'ambil di toko', 'ambil sendiri', 'pickup', 'pick up'])) {
           return $this->getSettingOrStore(
               'faq_pengambilan_di_toko',
               "**Pengambilan di Toko:**\nAnda dapat memilih opsi 'Ambil di Toko' saat checkout jika ingin mengambil pesanan Anda langsung ke workshop kami setelah status pengerjaan berubah menjadi 'Siap Diambil'.",
               'FAQ Pengambilan di Toko'
           );
       }

       // 16. Realtime Banners Query
       if (Str::contains($message, ['banner promo', 'promo aktif', 'promosi', 'ada promo'])) {
           $banners = \App\Models\CmsBanner::where('is_active', true)->get();
           if ($banners->isNotEmpty()) {
               $reply = "🎉 **Promo Aktif Kami:**\n\n";
               foreach ($banners as $b) {
                   $reply .= "- **{$b->title}**" . ($b->link ? " → {$b->link}" : '') . "\n";
               }
               return $reply;
           }
           return "Saat ini belum ada promo aktif. Pantau terus ya! 😊";
       }

       // 17. Produk Terbaru
       if (Str::contains($message, ['produk terbaru', 'terbaru', 'katalog baru', 'produk baru'])) {
           $products = Product::with('category')->latest('id')->take(5)->get();
           if ($products->isNotEmpty()) {
               return $this->renderCompactProductList("🆕 **Produk Terbaru Kami:**", $products);
           }
       }

       // 18. Produk Terlaris
       if (Str::contains($message, ['produk terlaris', 'terlaris', 'paling laris', 'paling laku', 'rekomendasi'])) {
           $products = Product::with('category')->get()->sortByDesc('sales_count')->take(5);
           if ($products->isNotEmpty()) {
               return $this->renderCompactProductList("🏆 **Produk Terlaris Kami:**", $products);
           }
       }

       // 19. Produk Promo/Diskon
       if (Str::contains($message, ['produk promo', 'ada diskon', 'diskon', 'produk murah', 'harga promo'])) {
           $products = Product::with('category')
               ->where(function($q) {
                   $q->where('discount_percent', '>', 0)->orWhere('discount_flat', '>', 0);
               })
               ->take(5)->get();
           if ($products->isNotEmpty()) {
               return $this->renderCompactProductList("💸 **Produk Diskon/Promo:**", $products);
           }
           return "Saat ini belum ada produk diskon. Pantau terus katalog kami ya!";
       }

       // 20. Kategori
       if (Str::contains($message, ['kategori', 'jenis produk', 'apa saja produk', 'produk apa saja'])) {
           $categories = Category::all();
           if ($categories->isNotEmpty()) {
               $reply = "📂 **Kategori produk yang tersedia:**\n";
               foreach ($categories as $cat) {
                   $reply .= "- {$cat->category_name}\n";
               }
               return $reply;
           }
       }

       // 21. Status Pesanan Customer (Isolasi Keamanan — data hanya milik user bersangkutan)
       if ($user && Str::contains($message, ['pesanan saya', 'status pesanan', 'lacak pesanan', 'order saya', 'cek pesanan', 'pesanan ku'])) {
           $orders = Order::where('user_id', $user->id)->latest('id')->take(5)->get();
           if ($orders->isEmpty()) {
               return "Anda belum memiliki riwayat pesanan di database kami.";
           }

           $reply = "**Daftar 5 Pesanan Terbaru Anda:**\n\n";
           foreach ($orders as $order) {
               $statusVal = $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status;
               $statusLabel = match($statusVal) {
                   'pending' => 'Menunggu Pembayaran',
                   'diproses' => 'Sedang Diproses',
                   'dicetak' => 'Sedang Dicetak',
                   'dikirim' => 'Sedang Dikirim',
                   'selesai' => 'Selesai',
                   'dibatalkan' => 'Dibatalkan',
                   default => ucfirst($statusVal ?? ''),
               };

               $reply .= "📦 **#{$order->invoice_number}**\n" .
                         "- Tanggal: " . $order->created_at->translatedFormat('d M Y H:i') . "\n" .
                         "- Status: {$statusLabel}\n" .
                         "- Kurir: " . strtoupper($order->shipping_courier ?: '-') . " (" . strtoupper($order->shipping_service ?: '-') . ")\n" .
                         "- Resi: " . ($order->tracking_number ?: 'Belum tersedia') . "\n" .
                         "- Total: Rp " . number_format($order->total_price, 0, ',', '.') . "\n\n";
           }
           return $reply;
       }

       // 22. Nomor Resi Spesifik
       if ($user && Str::contains($message, ['resi', 'nomor resi', 'cek resi', 'tracking'])) {
           $order = Order::where('user_id', $user->id)->latest('id')->first();
           if (!$order) {
               return "Anda belum memiliki riwayat pesanan di database kami.";
           }

           if ($order->tracking_number) {
               return "Nomor resi untuk pesanan terbaru Anda **#{$order->invoice_number}** adalah: **{$order->tracking_number}** (Kurir: " . strtoupper($order->shipping_courier) . ").";
           }
           $statusVal = $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status;
           return "Nomor resi untuk pesanan terbaru Anda **#{$order->invoice_number}** belum tersedia karena status pesanan Anda saat ini adalah: **" . ucfirst($statusVal ?? '') . "**.";
       }

       // 23. Pencarian Produk Langsung Berdasarkan Nama
       // Bersihkan filter kata kunci dari noise/filler words
       $keyword = $message;
       $noiseWords = [
           'apakah', 'ada', 'berapa', 'tanya', 'informasi', 'harga', 'stok', 'bahan', 'berat', 'deskripsi',
           'produk', 'untuk', 'dari', 'di', 'ukuran', 'kategori', 'detail', 'variant', 'cari', 'jual',
           'beli', 'pesan', 'dong', 'sih', 'ya', 'kak', 'min', 'admin', 'tolong', 'mau', 'ingin',
           'pengen', 'tahu', 'tau', 'info', 'halo', 'hai', 'hallo', 'apa', 'yang', 'dan', 'atau',
           'saya', 'aku', 'kamu', 'kalian', 'kami', 'kita', 'ini', 'itu', 'nya', 'lah', 'deh'
       ];
       foreach ($noiseWords as $word) {
           $keyword = preg_replace('/\b' . preg_quote($word, '/') . '\b/u', '', $keyword);
       }
       $keyword = trim(preg_replace('/\s+/', ' ', $keyword));

       // Minimal 3 karakter dan bukan kata-kata umum agar tidak salah deteksi
       $stopWords = ['kak', 'min', 'halo', 'hai', 'hallo', 'hay', 'selamat', 'print', 'cetak', 'bikin'];
       if (strlen($keyword) >= 3 && !in_array($keyword, $stopWords)) {
           // 1. LIKE search by product name
           $products = Product::with(['category', 'variants'])
               ->where('product_name', 'LIKE', "%{$keyword}%")
               ->get();

           // 2. Similarity search (Levenshtein) jika LIKE tidak ada hasil
           if ($products->isEmpty()) {
               $allProducts = Product::with(['category', 'variants'])->get();
               $bestDistance = 9999;
               $bestProduct = null;

               foreach ($allProducts as $p) {
                   $words = explode(' ', strtolower($p->product_name));
                   foreach ($words as $word) {
                       if (strlen($word) < 3) continue; // skip kata pendek
                       $dist = levenshtein(strtolower($keyword), $word);
                       if ($dist < $bestDistance) {
                           $bestDistance = $dist;
                           $bestProduct = $p;
                       }
                   }
               }

               // Ambil hanya jika sangat mirip
               if ($bestProduct && $bestDistance <= 2) {
                   $products = collect([$bestProduct]);
               }
           }

           // Render hasil
           if ($products->isNotEmpty()) {
               if ($products->count() === 1) {
                   return $this->renderDetailedProductCard($products->first());
               } else {
                   return $this->renderCompactProductList("Ditemukan beberapa produk yang cocok:", $products->take(5));
               }
           }
       }

       // Jika tidak ada yang cocok → kembalikan null (diteruskan ke Admin, tidak ada fallback)
       return null;
    }

    /**
     * Render compact product list.
     */
    private function renderCompactProductList(string $title, $products): string
    {
        $html = "{$title}<br><br>";
        foreach ($products as $prod) {
            $imageUrl = $prod->image ? asset('storage/' . $prod->image) : 'https://placehold.co/100';
            $prodUrl = route('produk.show', $prod->id);
            $isPromo = $prod->final_price < $prod->price;
            
            $priceHtml = $isPromo
                ? "<span class='text-slate-400 line-through text-[9px]'>Rp " . number_format($prod->price, 0, ',', '.') . "</span> <span class='text-orange-500 font-bold text-xs'>Rp " . number_format($prod->final_price, 0, ',', '.') . "</span>"
                : "<span class='text-orange-500 font-bold text-xs'>Rp " . number_format($prod->price, 0, ',', '.') . "</span>";

            $html .= "<div class='product-card bg-white border border-slate-200 rounded-xl p-3 mb-2 flex gap-3 shadow-3xs'>" .
                     "<img src='{$imageUrl}' class='w-12 h-12 object-cover rounded-lg border border-slate-100 flex-shrink-0'>" .
                     "<div class='flex-1 min-w-0 text-left'>" .
                     "<h4 class='font-bold text-slate-800 text-xs truncate'>{$prod->product_name}</h4>" .
                     "<div class='mt-0.5'>{$priceHtml}</div>" .
                     "</div>" .
                     "<a href='{$prodUrl}' class='btn-xs bg-orange-500 hover:bg-orange-650 text-white font-extrabold text-[9px] px-2.5 py-1.5 rounded-lg flex-shrink-0 uppercase tracking-wider text-center'>Lihat</a>" .
                     "</div>";
        }
        return $html;
    }

    /**
     * Render detailed product card.
     */
    private function renderDetailedProductCard(Product $product): string
    {
        $imageUrl = $product->image ? asset('storage/' . $product->image) : 'https://placehold.co/100';
        $productUrl = route('produk.show', $product->id);
        $isPromo = $product->final_price < $product->price;

        $priceHtml = $isPromo
            ? "<div>" .
              "<span class='text-slate-400 line-through text-[10px] mr-1.5'>Rp " . number_format($product->price, 0, ',', '.') . "</span>" .
              "<span class='text-orange-500 font-bold text-sm'>Rp " . number_format($product->final_price, 0, ',', '.') . "</span>" .
              "<span class='ml-1.5 bg-rose-100 text-rose-600 font-extrabold text-[8px] px-1.5 py-0.5 rounded'>PROMO</span>" .
              "</div>"
            : "<div><span class='text-orange-500 font-bold text-sm'>Rp " . number_format($product->price, 0, ',', '.') . "</span></div>";

        // Variants listing
        $variantsList = $product->variants;
        $variantsText = '-';
        if ($variantsList->isNotEmpty()) {
            $variantsText = $variantsList->pluck('variant_name')->unique()->implode(', ');
        }

        $stockStatus = $product->stock > 0 ? 'Tersedia' : 'Habis';
        $stockBadgeClass = $product->stock > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50';

        return "<div class='product-card bg-white border border-slate-200 rounded-xl p-3 flex gap-3 shadow-xs mt-1'>" .
               "<img src='{$imageUrl}' class='w-16 h-16 object-cover rounded-lg border border-slate-100 flex-shrink-0'>" .
               "<div class='flex-1 min-w-0 text-left'>" .
               "<h4 class='font-bold text-slate-800 text-xs truncate mb-1'>{$product->product_name}</h4>" .
               "<span class='text-[8px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider'>{$product->category->category_name}</span>" .
               
               "<div class='mt-2'>{$priceHtml}</div>" .
               
               "<div class='text-[10px] text-slate-500 mt-2 space-y-1'>" .
               "<div>📐 **Dimensi:** {$product->length} x {$product->width} x {$product->height} cm</div>" .
               "<div>🎨 **Varian:** {$variantsText}</div>" .
               "<div>⚖️ **Berat:** {$product->weight} gr | 📦 **Stok:** {$product->stock} (<span class='px-1.5 py-0.2 rounded font-semibold {$stockBadgeClass}'>{$stockStatus}</span>)</div>" .
               "</div>" .
               
               "<div class='text-[10px] text-slate-600 mt-2 italic border-t border-dashed border-slate-100 pt-2 line-clamp-3'>{$product->description}</div>" .
               
               "<div class='mt-3'>" .
               "<a href='{$productUrl}' class='inline-block btn-xs bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[9px] px-3.5 py-2 rounded-lg uppercase tracking-wider text-center shadow-3xs transition-colors'>Lihat Detail Produk</a>" .
               "</div>" .
               "</div>" .
               "</div>";
    }
}
