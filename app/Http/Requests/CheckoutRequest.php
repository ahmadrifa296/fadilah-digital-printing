<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Tentukan apakah user berhak melakukan request ini.
     *
     * Lapisan 1: Hanya user yang sudah login (auth()->check()).
     * Lapisan 2: Route middleware 'auth' (ditambahkan di Tahap 9) sebagai garda terdepan.
     * Lapisan 3: Di dalam controller, seluruh query cart menggunakan where('user_id', Auth::id())
     *            sehingga tidak ada cart user lain yang bisa diakses.
     *
     * Jika authorize() mengembalikan false, Laravel otomatis melempar
     * AuthorizationException (HTTP 403) sebelum rules() dijalankan.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi untuk form checkout.
     *
     * Catatan: cart_items dan total tidak divalidasi di sini karena
     * diambil langsung dari database (bukan dari input user), sehingga
     * tidak bisa dimanipulasi oleh pengguna.
     */
    public function rules(): array
    {
        $isDirect = $this->input('is_direct') == '1';
        $designRequired = false;

        if ($isDirect) {
            $buyNowData = session('buy_now');
            if ($buyNowData) {
                $product = \App\Models\Product::find($buyNowData['product_id'] ?? null);
                if ($product && $product->requires_design_file) {
                    $designRequired = true;
                }
            }
        } else {
            $designRequired = \App\Models\Cart::where('user_id', auth()->id())
                ->whereHas('product', function ($query) {
                    $query->where('requires_design_file', true);
                })->exists();
        }

        $rules = [
            'address_id'          => ['required', 'exists:user_addresses,id,user_id,' . auth()->id()],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'shipping_courier'    => ['required', 'string'],
            'shipping_service'    => ['required', 'string'],
            'shipping_cost'       => ['required', 'integer', 'min:0'],
            'shipping_estimation' => ['required', 'string'],
        ];

        $rules['design_file'] = [
            $designRequired ? 'required' : 'nullable',
            'file',
            'mimes:jpeg,png,jpg,pdf,ai,cdr,psd,zip,rar',
            'max:51200', // 50MB in KB
        ];

        return $rules;
    }

    /**
     * Pesan error yang ramah pengguna (dalam Bahasa Indonesia).
     */
    public function messages(): array
    {
        return [
            'address_id.required' => 'Silakan pilih alamat pengiriman terlebih dahulu.',
            'address_id.exists'   => 'Alamat pengiriman tidak valid.',
            'design_file.required' => 'Produk ini memerlukan file desain. Silakan upload file terlebih dahulu.',
            'design_file.file'   => 'File desain harus berupa file yang valid.',
            'design_file.mimes'  => 'File desain harus berformat: JPG, JPEG, PNG, PDF, AI, CDR, PSD, ZIP, atau RAR.',
            'design_file.max'    => 'Ukuran file desain tidak boleh melebihi 50 MB.',
            'notes.string'       => 'Catatan pesanan harus berupa teks.',
            'notes.max'          => 'Catatan pesanan tidak boleh lebih dari 1.000 karakter.',
        ];
    }

    /**
     * Nama atribut untuk pesan error yang lebih deskriptif.
     */
    public function attributes(): array
    {
        return [
            'address_id'  => 'alamat pengiriman',
            'design_file' => 'file desain',
            'notes'       => 'catatan pesanan',
        ];
    }
}
