<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Services\ProductPriceCalculatorService;

class CartController extends Controller
{
    protected $calculator;

    public function __construct(ProductPriceCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Tampilkan isi keranjang belanja milik user yang sedang login.
     * Route: GET /cart (ditambahkan di Tahap 9)
     */
    public function index(): View
    {
        $cartItems = Cart::with('product.category')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $cartTotal = $cartItems->sum('subtotal');
        $cartCount = $cartItems->count();

        return view('cart.index', compact('cartItems', 'cartTotal', 'cartCount'));
    }

    /**
     * Tambah produk ke keranjang belanja.
     * Route: POST /cart (ditambahkan di Tahap 9)
     *
     * Logika:
     *   - Jika produk belum ada di cart → buat baris baru dengan snapshot harga.
     *   - Jika produk sudah ada di cart → tambahkan qty dan recalculate subtotal.
     *   - Snapshot harga (price) diambil dari harga produk saat ini agar tidak
     *     terpengaruh perubahan harga oleh admin di kemudian hari.
     */
    public function store(Request $request): RedirectResponse
    {
        $product = Product::findOrFail($request->product_id);

        // Validasi ketersediaan stok
        if ($product->stock < 1) {
            return back()->with('error', "Maaf, stok {$product->product_name} sedang habis!");
        }

        $isSizeBased = in_array($product->calculation_type, ['custom_size', 'quantity_custom_size']);
        $minLen = $product->min_length ?: 0.1;
        $maxLen = $product->max_length ?: 100.0;
        $minWid = $product->min_width ?: 0.1;
        $maxWid = $product->max_width ?: 100.0;
        $minOrder = $product->minimum_order ?: 1;
        $maxOrder = $product->maximum_order ?: 99999;

        $rules = [
            'product_id' => 'required|exists:products,id',
            'notes'      => 'nullable|string|max:500',
            'ukuran'     => 'nullable|string|max:255',
            'bahan'      => 'nullable|string|max:255',
            'finishing'  => 'nullable|string|max:255',
            'custom_text'=> 'nullable|string|max:500',
            'design_file'=> 'nullable|file|mimes:pdf,jpg,png,jpeg,ai,cdr,psd|max:10240', // Maksimal 10MB
            'qty' => 'required|integer|min:' . $minOrder . '|max:' . $maxOrder,
        ];

        if ($isSizeBased) {
            $rules['custom_length'] = 'required|numeric|gt:0|min:' . $minLen . '|max:' . $maxLen;
            $rules['custom_width'] = 'required|numeric|gt:0|min:' . $minWid . '|max:' . $maxWid;
        }

        $request->validate($rules);

        $ukuran = $request->ukuran;
        $bahan = $request->bahan;
        $finishing = $request->finishing;
        $notes = $request->notes;
        $customText = $request->custom_text;
        $customLength = $isSizeBased ? $request->custom_length : null;
        $customWidth = $isSizeBased ? $request->custom_width : null;

        $designPath = null;
        if ($request->hasFile('design_file')) {
            $designPath = $request->file('design_file')->store('designs', 'public');
        }

        // Calculate pricing using the centralized service
        $calcResult = $this->calculator->calculate($product, [
            'qty' => $request->qty,
            'custom_length' => $customLength,
            'custom_width' => $customWidth,
            'ukuran' => $ukuran,
            'bahan' => $bahan,
            'finishing' => $finishing,
        ]);

        $finalUnitPrice = $calcResult['unit_price'];
        $finalSubtotal = $calcResult['subtotal'];
        $customArea = $calcResult['area'];
        $pricePerM2 = $calcResult['final_price_per_m2'];

        // Cek apakah produk dengan variasi yang persis sama sudah ada di cart user ini
        $existingCart = Cart::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->where('ukuran', $ukuran)
            ->where('bahan', $bahan)
            ->where('finishing', $finishing)
            ->where('custom_length', $customLength)
            ->where('custom_width', $customWidth)
            ->first();

        if ($existingCart) {
            $newQty = $existingCart->qty + $request->qty;

            // Recalculate using the service for the new quantity
            $recalcResult = $this->calculator->calculate($product, [
                'qty' => $newQty,
                'custom_length' => $customLength,
                'custom_width' => $customWidth,
                'ukuran' => $ukuran,
                'bahan' => $bahan,
                'finishing' => $finishing,
            ]);

            // Validasi total qty tidak melebihi stok
            if (!$isSizeBased && $newQty > $product->stock) {
                return back()->with('error',
                    "Total qty melebihi stok yang tersedia. " .
                    "Di cart: {$existingCart->qty}, Stok: {$product->stock}."
                );
            }

            // Update qty & recalculate subtotal
            $existingCart->update([
                'qty'      => $newQty,
                'price'    => $recalcResult['unit_price'],
                'subtotal' => $recalcResult['subtotal'],
                'custom_area' => $recalcResult['area'],
                'price_per_m2' => $recalcResult['final_price_per_m2'],
                'notes'    => $notes ?: $existingCart->notes,
                'design_file' => $designPath ?: $existingCart->design_file,
            ]);

            return back()->with('success', "{$product->product_name} berhasil diperbarui di keranjang!");
        }

        // Buat item baru di cart
        Cart::create([
            'user_id'    => Auth::id(),
            'product_id' => $product->id,
            'qty'        => $request->qty,
            'price'      => $finalUnitPrice,
            'subtotal'   => $finalSubtotal,
            'notes'      => $notes,
            'ukuran'     => $ukuran,
            'custom_length' => $customLength,
            'custom_width' => $customWidth,
            'custom_area' => $customArea,
            'price_per_m2' => $pricePerM2,
            'bahan'      => $bahan,
            'finishing'  => $finishing,
            'custom_text'=> $customText,
            'design_file'=> $designPath,
        ]);

        return back()->with('success', "{$product->product_name} berhasil ditambahkan ke keranjang!");
    }

    /**
     * Update jumlah (qty) item di keranjang.
     * Route: PUT /cart/{cart} (ditambahkan di Tahap 9)
     *
     * Subtotal dihitung ulang dari price SNAPSHOT, bukan dari harga produk saat ini.
     */
    public function update(Request $request, Cart $cart): RedirectResponse
    {
        // Pastikan user hanya bisa mengubah cart miliknya sendiri
        if ($cart->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah cart ini.');
        }

        $product = $cart->product;
        $minOrder = $product->minimum_order ?: 1;
        $maxOrder = $product->maximum_order ?: 99999;

        $request->validate([
            'qty' => 'required|integer|min:' . $minOrder . '|max:' . $maxOrder,
        ]);

        if ($request->qty > $product->stock && !in_array($product->calculation_type, ['custom_size', 'quantity_custom_size'])) {
            return back()->with('error',
                "Stok {$product->product_name} hanya tersisa {$product->stock} item!"
            );
        }

        // Recalculate using centralized calculator
        $calcResult = $this->calculator->calculate($product, [
            'qty' => $request->qty,
            'custom_length' => $cart->custom_length,
            'custom_width' => $cart->custom_width,
            'ukuran' => $cart->ukuran,
            'bahan' => $cart->bahan,
            'finishing' => $cart->finishing,
        ]);

        $cart->update([
            'qty'      => $request->qty,
            'price'    => $calcResult['unit_price'],
            'subtotal' => $calcResult['subtotal'],
            'custom_area' => $calcResult['area'],
            'price_per_m2' => $calcResult['final_price_per_m2'],
        ]);

        return back()->with('success', 'Jumlah item berhasil diperbarui!');
    }

    /**
     * Hapus satu item dari keranjang belanja.
     * Route: DELETE /cart/{cart} (ditambahkan di Tahap 9)
     */
    public function destroy(Cart $cart): RedirectResponse
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus cart ini.');
        }

        $productName = $cart->product->product_name;
        $cart->delete();

        return back()->with('success', "{$productName} berhasil dihapus dari keranjang!");
    }

    /**
     * Kosongkan seluruh isi keranjang belanja user.
     * Route: POST /cart/clear (ditambahkan di Tahap 9)
     */
    public function clear(): RedirectResponse
    {
        $deleted = Cart::where('user_id', Auth::id())->delete();

        if ($deleted > 0) {
            return back()->with('success', 'Keranjang belanja berhasil dikosongkan!');
        }

        return back()->with('info', 'Keranjang belanja sudah kosong.');
    }
}
