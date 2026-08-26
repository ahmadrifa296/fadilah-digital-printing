<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ProductPriceCalculatorService;

class CheckoutController extends Controller
{
    protected $calculator;

    public function __construct(ProductPriceCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Tampilkan halaman ringkasan checkout dari cart.
     * Route: GET /checkout (akan ditambahkan di Tahap 9)
     *
     * Index hanya membaca data — tidak perlu transaksi DB.
     * Authorization: hanya user login (via auth middleware di Tahap 9).
     */
    public function index(): View|RedirectResponse
    {
        $cartItems = Cart::with(['product.category'])
            ->where('user_id', Auth::id()) // authorization: hanya cart milik user sendiri
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja masih kosong!');
        }

        $cartTotal = $cartItems->sum('subtotal');
        $cartCount = $cartItems->sum('qty');
        $isDirect = false;

        $designRequired = $cartItems->contains(function ($item) {
            return $item->product->requires_design_file;
        });

        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get();

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartCount', 'addresses', 'designRequired', 'isDirect'));
    }

    /**
     * Tampilkan halaman checkout langsung (Buy Now) dari session.
     */
    public function directCheckout(Request $request): View|RedirectResponse
    {
        $buyNowData = session('buy_now');

        if (!$buyNowData) {
            return redirect()->route('cart.index')
                ->with('error', 'Tidak ada transaksi langsung yang sedang berlangsung.');
        }

        $product = Product::with(['category'])->find($buyNowData['product_id']);
        if (!$product) {
            session()->forget('buy_now');
            return redirect()->route('cart.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        $options = $buyNowData['options'] ?? [];
        $qty = (int) ($buyNowData['quantity'] ?? 1);

        $calcResult = $this->calculator->calculate($product, [
            'qty' => $qty,
            'custom_length' => $options['custom_length'] ?? null,
            'custom_width' => $options['custom_width'] ?? null,
            'ukuran' => $options['ukuran'] ?? null,
            'bahan' => $options['bahan'] ?? null,
            'finishing' => $options['finishing'] ?? null,
        ]);

        $finalUnitPrice = $calcResult['unit_price'];

        $item = (object)[
            'product'     => $product,
            'product_id'  => $product->id,
            'ukuran'      => $options['ukuran'] ?? null,
            'custom_length' => $options['custom_length'] ?? null,
            'custom_width' => $options['custom_width'] ?? null,
            'custom_area' => $calcResult['area'],
            'price_per_m2' => $calcResult['final_price_per_m2'],
            'bahan'       => $options['bahan'] ?? null,
            'finishing'   => $options['finishing'] ?? null,
            'custom_text' => $options['custom_text'] ?? null,
            'price'       => $finalUnitPrice,
            'qty'         => $qty,
            'subtotal'    => $calcResult['subtotal'],
            'design_file' => null,
            'notes'       => $options['notes'] ?? null,
        ];

        $cartItems = collect([$item]);
        $cartTotal = $calcResult['subtotal'];
        $cartCount = $qty;
        $isDirect = true;

        $designRequired = $product->requires_design_file;

        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get();

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartCount', 'addresses', 'designRequired', 'isDirect'));
    }

    /**
     * Simpan data produk ke session Beli Sekarang dan redirect ke direct checkout.
     */
    public function buyNow(Request $request, Product $product): RedirectResponse
    {
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
            'notes'      => 'nullable|string|max:500',
            'ukuran'     => 'nullable|string|max:255',
            'bahan'      => 'nullable|string|max:255',
            'finishing'  => 'nullable|string|max:255',
            'custom_text'=> 'nullable|string|max:500',
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
        $customText = $request->custom_text;
        $notes = $request->notes;
        $customLength = $isSizeBased ? $request->custom_length : null;
        $customWidth = $isSizeBased ? $request->custom_width : null;

        session([
            'buy_now' => [
                'product_id' => $product->id,
                'quantity'   => (int) $request->qty,
                'options'    => array_merge(
                    $request->only(['ukuran', 'bahan', 'finishing', 'custom_text', 'notes']),
                    ['custom_length' => $customLength, 'custom_width' => $customWidth]
                ),
            ]
        ]);

        return redirect()->route('checkout.direct');
    }

    /**
     * Proses checkout: konversi cart atau session buy now menjadi Order + OrderDetail.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $isDirect = $request->input('is_direct') == '1';
                $cartItems = collect();
                $lockedProducts = collect();

                if ($isDirect) {
                    $buyNowData = session('buy_now');
                    if (!$buyNowData) {
                        throw new \DomainException('Transaksi Beli Sekarang kadaluarsa atau tidak ditemukan.');
                    }

                    $product = Product::lockForUpdate()->findOrFail($buyNowData['product_id']);
                    $qty = (int) ($buyNowData['quantity'] ?? 1);
                    if ($product->stock < $qty) {
                        throw new \DomainException(
                            "Stok '{$product->product_name}' tidak mencukupi. " .
                            "Tersedia: {$product->stock} item, Dipesan: {$qty} item."
                        );
                    }

                    $options = $buyNowData['options'] ?? [];
                    
                    $calcResult = $this->calculator->calculate($product, [
                        'qty' => $qty,
                        'custom_length' => $options['custom_length'] ?? null,
                        'custom_width' => $options['custom_width'] ?? null,
                        'ukuran' => $options['ukuran'] ?? null,
                        'bahan' => $options['bahan'] ?? null,
                        'finishing' => $options['finishing'] ?? null,
                    ]);

                    $finalUnitPrice = $calcResult['unit_price'];

                    $item = (object)[
                        'product'     => $product,
                        'product_id'  => $product->id,
                        'ukuran'      => $options['ukuran'] ?? null,
                        'custom_length' => $options['custom_length'] ?? null,
                        'custom_width' => $options['custom_width'] ?? null,
                        'custom_area' => $calcResult['area'],
                        'price_per_m2' => $calcResult['final_price_per_m2'],
                        'bahan'       => $options['bahan'] ?? null,
                        'finishing'   => $options['finishing'] ?? null,
                        'custom_text' => $options['custom_text'] ?? null,
                        'price'       => $finalUnitPrice,
                        'qty'         => $qty,
                        'subtotal'    => $calcResult['subtotal'],
                        'design_file' => null,
                    ];
                    $cartItems->push($item);
                    $lockedProducts->put($product->id, $product);
                } else {
                    $cartItems = Cart::with('product')
                        ->where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->get();

                    if ($cartItems->isEmpty()) {
                        throw new \DomainException('Keranjang belanja kosong, tidak dapat checkout.');
                    }

                    foreach ($cartItems as $item) {
                        $product = Product::lockForUpdate()->findOrFail($item->product_id);

                        if ($product->stock < $item->qty) {
                            throw new \DomainException(
                                "Stok '{$product->product_name}' tidak mencukupi. " .
                                "Tersedia: {$product->stock} item, Dipesan: {$item->qty} item. " .
                                "Silakan perbarui keranjang belanja Anda."
                            );
                        }

                        $lockedProducts->put($item->product_id, $product);
                    }
                }

                // Upload file desain dengan UUID
                $designFilePath = null;
                if ($request->hasFile('design_file')) {
                    $file = $request->file('design_file');
                    $extension = $file->getClientOriginalExtension();
                    $filename = \Illuminate\Support\Str::uuid() . '.' . $extension;
                    $designFilePath = $file->storeAs('designs', $filename, 'public');
                }

                $invoiceNumber = Order::generateInvoiceNumber();

                // Get selected shipping address
                $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($request->address_id);

                $subtotal = $cartItems->sum('subtotal');
                $shippingCost = (int) $request->shipping_cost;
                $totalPrice = $subtotal + $shippingCost;

                $order = Order::create([
                    'user_id'        => Auth::id(),
                    'invoice_number' => $invoiceNumber,
                    'order_status'   => 'pending',
                    'payment_status' => 'pending',
                    'total_price'    => $totalPrice,
                    'design_file'    => $designFilePath,
                    'notes'          => $request->notes,
                    'status'         => 'pending',

                    // Alamat Pengiriman Snapshot
                    'receiver_name'  => $address->receiver_name,
                    'phone'          => $address->phone,
                    'province'       => $address->province,
                    'city'           => $address->city,
                    'district'       => $address->district,
                    'subdistrict'    => $address->subdistrict,
                    'postal_code'    => $address->postal_code,
                    'rt'             => $address->rt,
                    'rw'             => $address->rw,
                    'no_rumah'       => $address->no_rumah,
                    'patokan'        => $address->patokan,
                    'full_address'   => $address->full_address,
                    'address_label'  => $address->label,

                    // Biteship Shipping Snapshot
                    'shipping_cost'       => $shippingCost,
                    'shipping_courier'    => $request->shipping_courier,
                    'shipping_service'    => $request->shipping_service,
                    'shipping_estimation' => $request->shipping_estimation,
                    'shipping_status'     => 'pending',
                ]);

                foreach ($cartItems as $item) {
                    OrderDetail::create([
                        'order_id'    => $order->id,
                        'product_id'  => $item->product_id,
                        'qty'         => $item->qty,
                        'subtotal'    => $item->subtotal,
                        'design_file' => $item->design_file ?: $order->design_file,
                        'custom_text' => $item->custom_text,
                        'ukuran'      => $item->ukuran,
                        'custom_length' => $item->custom_length ?? null,
                        'custom_width' => $item->custom_width ?? null,
                        'custom_area' => $item->custom_area ?? null,
                        'price_per_m2' => $item->price_per_m2 ?? null,
                        'bahan'       => $item->bahan,
                        'finishing'   => $item->finishing,
                        'estimasi_pengerjaan' => '1-3 Hari',
                    ]);

                    $lockedProducts->get($item->product_id)->decrement('stock', $item->qty);

                    StockLog::create([
                        'product_id' => $item->product_id,
                        'type' => 'out',
                        'quantity' => $item->qty,
                        'description' => "Pengurangan stok otomatis akibat checkout pesanan #{$invoiceNumber}",
                        'user_id' => Auth::id(),
                    ]);
                }

                if ($isDirect) {
                    session()->forget('buy_now');
                } else {
                    Cart::where('user_id', Auth::id())->delete();
                }

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Checkout Sukses',
                    'description' => "Customer melakukan checkout pesanan #{$invoiceNumber} dengan total: Rp " . number_format($totalPrice, 0, ',', '.'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $order;
            });

            // Notify customer
            $customer = Auth::user();
            $customer->notify(new \App\Notifications\AppNotification(
                'Pesanan Berhasil Dibuat',
                "Pesanan Anda #{$order->invoice_number} berhasil dibuat. Silakan lakukan pembayaran.",
                'shopping-bag',
                'blue',
                route('dashboard') . '?tab=pesanan',
                'pesanan'
            ));

            // Notify admins/owners
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AppNotification(
                    'Pesanan Baru Masuk',
                    "Pesanan baru #{$order->invoice_number} dari {$customer->name} memerlukan pembayaran.",
                    'shopping-cart',
                    'orange',
                    route('pesanan.show', $order->id),
                    'pesanan'
                ));
            }

            // ---------------------------------------------------------------- //
            // Redirect ke halaman payment setelah checkout berhasil.
            // ---------------------------------------------------------------- //
            return redirect()->route('payment.pay', ['order' => $order->id])
                ->with('success',
                    "Pesanan {$order->invoice_number} berhasil dibuat! " .
                    "Silakan lakukan pembayaran."
                );

        } catch (\DomainException $e) {
            // ---------------------------------------------------------------- //
            // Business logic error yang sudah diketahui dan diharapkan:
            //   - Cart kosong (setelah transaction mulai)
            //   - Stok tidak mencukupi
            //
            // Transaction sudah ROLLBACK otomatis.
            // Tidak perlu log — ini bukan system error.
            // Pesan diteruskan langsung ke user.
            // ---------------------------------------------------------------- //
            return back()->with('error', $e->getMessage());

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // ---------------------------------------------------------------- //
            // Race condition: dua transaksi serentak menghasilkan invoice_number
            // yang sama (sangat jarang terjadi karena ada lockForUpdate).
            //
            // Log dengan severity WARNING — bukan error sistem, tapi perlu dipantau.
            // ---------------------------------------------------------------- //
            Log::warning('Checkout: Konflik invoice_number terdeteksi', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error',
                'Terjadi konflik nomor invoice. Silakan coba checkout kembali dalam beberapa detik.'
            );

        } catch (\Throwable $e) {
            // ---------------------------------------------------------------- //
            // Unexpected system error (koneksi DB, disk penuh, dll).
            //
            // Log::error() dengan konteks lengkap untuk debugging:
            //   - user_id: siapa yang sedang checkout
            //   - exception: pesan error asli
            //   - file + line: lokasi error di kode
            //
            // Transaction sudah ROLLBACK otomatis.
            // Stack trace TIDAK ditampilkan ke user.
            // ---------------------------------------------------------------- //
            Log::error('Checkout: Unexpected system error', [
                'user_id'   => Auth::id(),
                'exception' => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return back()->with('error',
                'Terjadi kesalahan sistem saat memproses pesanan. ' .
                'Silakan coba lagi atau hubungi admin jika masalah berlanjut.'
            );
        }
    }

    public function getRates(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
        ]);

        try {
            $address = \App\Models\UserAddress::where('user_id', Auth::id())->findOrFail($request->address_id);

            if (empty($address->postal_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alamat pengiriman belum memiliki Kode Pos yang valid.'
                ]);
            }

            $isDirect = $request->input('is_direct') == '1';
            if ($isDirect) {
                $buyNow = session('buy_now');
                if (!$buyNow) {
                    return response()->json(['success' => false, 'message' => 'Transaksi Beli Sekarang kadaluarsa atau tidak ditemukan.']);
                }
                $product = Product::find($buyNow['product_id']);
                if (!$product) {
                    return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.']);
                }
                $options = $buyNow['options'] ?? [];
                $item = (object)[
                    'product' => $product,
                    'qty'     => $buyNow['quantity'],
                    'custom_length' => $options['custom_length'] ?? null,
                    'custom_width' => $options['custom_width'] ?? null,
                ];
                $cartItems = collect([$item]);
            } else {
                // Get Cart Items
                $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
                if ($cartItems->isEmpty()) {
                    return response()->json(['success' => false, 'message' => 'Keranjang belanja Anda kosong.']);
                }
            }

            // Map cart items into Biteship payload structure (with fallback defaults)
            $items = [];
            foreach ($cartItems as $item) {
                $product = $item->product;
                $weight = $product->weight > 0 ? $product->weight : 100;
                $isSizeBased = in_array($product->calculation_type, ['custom_size', 'quantity_custom_size']);
                if ($isSizeBased && isset($item->custom_length) && isset($item->custom_width)) {
                    $area = $item->custom_length * $item->custom_width;
                    $weight = (int) round($weight * $area);
                }

                $items[] = [
                    'name' => $product->product_name,
                    'description' => substr($product->description ?? 'Cetak Custom', 0, 50),
                    'value' => (int) $product->price,
                    'weight' => $weight,
                    'quantity' => (int) $item->qty,
                    'length' => (int) ($product->length > 0 ? $product->length : 10),    // Default 10 cm
                    'width' => (int) ($product->width > 0 ? $product->width : 10),       // Default 10 cm
                    'height' => (int) ($product->height > 0 ? $product->height : 5)       // Default 5 cm
                ];
            }

            // Define origin (using warehouse configuration in settings)
            $originPostal = setting('warehouse_postal_code');
            $originId = setting('warehouse_biteship_origin_id');
            
            if (empty($originPostal) && empty($originId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi asal pengiriman (Gudang) di Dashboard Admin belum lengkap (Origin ID / Kode Pos kosong).'
                ]);
            }

            $origin = [
                'postal_code' => $originPostal,
                'latitude' => setting('warehouse_latitude'),
                'longitude' => setting('warehouse_longitude')
            ];

            // Define destination (using customer selected address)
            $destination = [
                'postal_code' => $address->postal_code,
                'latitude' => $address->latitude,
                'longitude' => $address->longitude
            ];

            // Initialize Biteship Service
            $biteship = new \App\Services\BiteshipService();
            
            // Query standard couriers
            $couriers = 'jne,jnt,sicepat,anteraja,pos,tiki,ninja,lion,sap,ide';
            
            // Use cache for 10 minutes
            $cacheKey = 'biteship_rates_' . md5(json_encode($origin) . json_encode($destination) . json_encode($items));
            $rates = Cache::remember($cacheKey, 600, function () use ($biteship, $origin, $destination, $items, $couriers) {
                return $biteship->calculateRates($origin, $destination, $items, $couriers);
            });

            if (is_array($rates) && isset($rates['success']) && $rates['success'] === false) {
                $errMsg = $rates['body']['message'] ?? $rates['raw'] ?? 'Gagal mengambil data estimasi ongkir.';
                return response()->json([
                    'success' => false,
                    'message' => "Biteship Error (HTTP {$rates['status']}): {$errMsg}",
                    'debug' => [
                        'status' => $rates['status'],
                        'body' => $rates['body'],
                        'raw' => $rates['raw']
                    ]
                ]);
            }

            if (empty($rates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data estimasi ongkir. Respon tarif kosong dari Biteship.'
                ]);
            }

            return response()->json([
                'success' => true,
                'rates' => $rates
            ]);

        } catch (\Exception $e) {
            Log::error('Calculate rates exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ongkir. Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}
