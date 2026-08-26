<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected \App\Services\OrderWorkflowService $orderWorkflowService
    ) {}

    public function index(): View
    {
        Order::autoCompleteDeliveredOrders();
        $orders = Order::with(['user', 'orderDetails.product'])
            ->latest()
            ->get();
        return view('pesanan.index', compact('orders'));
    }

   /**
    * Proses pemesanan langsung dari halaman katalog (direct purchase, tanpa cart).
    *
    * PERBAIKAN BUG #2: Method ini sebelumnya hanya menyimpan ke tabel 'orders'
    * tanpa mengisi tabel 'order_details'. Sekarang diperbaiki menggunakan
    * DB::transaction() agar Order dan OrderDetail selalu konsisten.
    *
    * Catatan: Method ini akan digantikan oleh CheckoutController@store (via cart)
    * setelah route diperbarui di Tahap 9. Dipertahankan agar route existing
    * POST /checkout (checkout.store) tidak langsung rusak.
    */
   public function store(Request $request): RedirectResponse
   {
       $request->validate([
           'product_id'  => 'required|exists:products,id',
           'qty'         => 'nullable|integer|min:1|max:100',
           'notes'       => 'nullable|string|max:1000',
           'design_file' => 'required|file|mimes:jpeg,png,jpg,pdf,zip,rar|max:10240',
       ]);

       $product = Product::findOrFail($request->product_id);
       $qty     = (int) ($request->qty ?? 1);

       if ($product->stock < $qty) {
           return back()->with('error',
               "Maaf, stok {$product->product_name} hanya tersisa {$product->stock} item!"
           );
       }

       // Upload file desain sebelum memulai transaksi DB
       $filePath = null;
       if ($request->hasFile('design_file')) {
           $filePath = $request->file('design_file')->store('designs', 'public');
       }

       try {
           $order = DB::transaction(function () use ($request, $product, $qty, $filePath) {
               $invoiceNumber = Order::generateInvoiceNumber();
               $subtotal      = $product->price * $qty;

               // CREATE Order dengan kolom-kolom baru
               $order = Order::create([
                   'user_id'        => Auth::id(),
                   'invoice_number' => $invoiceNumber,
                   'order_status'   => 'pending',
                   'payment_status' => 'pending',
                   'total_price'    => $subtotal,
                   'design_file'    => $filePath,
                   'notes'          => $request->notes,
                   'status'         => 'pending', // backward compat kolom lama
               ]);

               // CREATE OrderDetail (FIX BUG #2 — sebelumnya tidak pernah diisi)
               OrderDetail::create([
                   'order_id'   => $order->id,
                   'product_id' => $product->id,
                   'qty'        => $qty,
                   'subtotal'   => $subtotal,
               ]);

               // Kurangi stok produk
               $product->decrement('stock', $qty);

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

           return back()->with('success',
               "Pesanan {$order->invoice_number} berhasil dikirim! Silakan tunggu konfirmasi Admin."
           );

       } catch (\Throwable $e) {
           report($e);
           return back()->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
       }
   }

   /**
    * Tampilkan detail satu pesanan.
    * Eager load relasi agar View tidak mengalami N+1.
    */
   public function show(Order $pesanan): View
   {
       $pesanan->load(['user', 'orderDetails.product', 'shipment.trackings']);
       return view('pesanan.show', compact('pesanan'));
   }

   /**
    * Update status pesanan oleh admin/owner.
    *
    * Backward Compatibility Strategy:
    *   View lama (pesanan/index.blade.php) masih mengirim field 'status' dengan
    *   nilai 'pending', 'proses', 'selesai'. Method ini menerima kedua format
    *   (lama dan baru) dan memetakannya ke 'order_status' yang benar.
    *   View akan diperbarui di Tahap 10 untuk mengirim 'order_status'.
    *
    * Kolom 'status' (ENUM lama) hanya diupdate jika nilai baru kompatibel
    * dengan ENUM ['pending','paid','cancelled'] untuk menghindari MySQL error.
    */
    public function update(Request $request, Order $pesanan): RedirectResponse
    {
        $request->validate([
            'order_status' => 'required|string',
        ]);

        try {
            $newStatus = \App\Enums\OrderStatus::from($request->order_status);
            $oldStatus = $pesanan->order_status;

            if ($newStatus !== $oldStatus) {
                // Gunakan workflow service untuk transisi status
                switch ($newStatus) {
                    case \App\Enums\OrderStatus::DIPROSES:
                        if ($pesanan->order_status === \App\Enums\OrderStatus::PENDING) {
                            $this->orderWorkflowService->markPaid($pesanan);
                        } else {
                            $pesanan->update(['order_status' => $newStatus]);
                        }
                        break;
                    case \App\Enums\OrderStatus::SEDANG_DICETAK:
                        if (!$this->orderWorkflowService->startPrinting($pesanan)) {
                            $pesanan->update(['order_status' => $newStatus]);
                            \App\Models\ActivityLog::create([
                                'user_id' => Auth::id(),
                                'activity' => 'Proses Cetak',
                                'description' => "Order #{$pesanan->invoice_number} berpindah ke Sedang Dicetak.",
                                'ip_address' => request()->ip() ?: '127.0.0.1',
                                'user_agent' => request()->userAgent() ?: 'System',
                            ]);
                        }
                        break;
                    case \App\Enums\OrderStatus::SIAP_DIKEMAS:
                        if ($pesanan->order_status === \App\Enums\OrderStatus::SEDANG_DICETAK) {
                            $this->orderWorkflowService->finishPrinting($pesanan);
                        } else {
                            $pesanan->update(['order_status' => $newStatus]);
                        }
                        break;
                    case \App\Enums\OrderStatus::DIKEMAS:
                        $pesanan->update([
                            'order_status' => $newStatus,
                            'status' => 'paid', // legacy
                        ]);
                        \App\Models\ActivityLog::create([
                            'user_id' => Auth::id(),
                            'activity' => 'Kemas Pesanan',
                            'description' => "Order #{$pesanan->invoice_number} telah dikemas (Siap dikirim).",
                            'ip_address' => request()->ip() ?: '127.0.0.1',
                            'user_agent' => request()->userAgent() ?: 'System',
                        ]);
                        break;
                    case \App\Enums\OrderStatus::DIKIRIM:
                        if ($pesanan->order_status === \App\Enums\OrderStatus::DIKEMAS) {
                            $this->orderWorkflowService->ship($pesanan);
                        } else {
                            $pesanan->update(['order_status' => $newStatus]);
                        }
                        break;
                    case \App\Enums\OrderStatus::SELESAI:
                        $this->orderWorkflowService->complete($pesanan, 'Admin');
                        break;
                    case \App\Enums\OrderStatus::DIBATALKAN:
                        $this->orderWorkflowService->cancel($pesanan);
                        break;
                    default:
                        $pesanan->update(['order_status' => $newStatus]);
                }
            }

            $tab = $request->query('tab');
            if ($request->header('referer') && str_contains($request->header('referer'), 'admin/shipping')) {
                return redirect()->route('admin.shipping.index')
                    ->with('success', 'Status pesanan berhasil diperbarui!');
            }

            if ($newStatus === \App\Enums\OrderStatus::SELESAI || $newStatus === \App\Enums\OrderStatus::DIBATALKAN) {
                return redirect()->route('transaksi.riwayat')
                    ->with('success', 'Status pesanan berhasil diperbarui!');
            }

            return redirect()->route('pesanan.index', $tab ? ['tab' => $tab] : [])
                ->with('success', 'Status pesanan berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

   /**
    * Halaman cetak nota / invoice.
    * Eager load relasi untuk menampilkan detail produk.
    */
   public function cetak(Order $pesanan): View
   {
       $pesanan->load(['user', 'orderDetails.product']);
       return view('pesanan.cetak', compact('pesanan'));
   }

   /**
    * Laporan penjualan — hanya order dengan order_status = 'selesai'.
    *
    * BUG FIX: Method ini sebelumnya tidak ada di controller (tapi ada di route),
    * sehingga navigasi ke /laporan-penjualan selalu error. Sekarang ditambahkan.
    */
    public function laporan(Request $request): View
    {
        $period = $request->input('period', 'all');

        $query = Order::with(['user', 'claim'])
            ->where('order_status', \App\Enums\OrderStatus::SELESAI);

        $dateLimit = null;
        if ($period === '1_month') {
            $dateLimit = now()->subMonth();
        } elseif ($period === '3_months') {
            $dateLimit = now()->subMonths(3);
        } elseif ($period === '6_months') {
            $dateLimit = now()->subMonths(6);
        } elseif ($period === '1_year') {
            $dateLimit = now()->subYear();
        }

        if ($dateLimit) {
            $query->where('created_at', '>=', $dateLimit);
        }

        $orders = $query->latest()->get();

        $totalPendapatanKotor = $orders->sum('total_price');

        // Fetch claims related to the filtered orders
        $claims = \App\Models\OrderClaim::with(['order.user'])
            ->whereIn('order_id', $orders->pluck('id'))
            ->latest()
            ->get();

        // Calculate total refund from approved claims
        $totalRefund = $claims->where('status', 'approved')->sum(function($c) {
            return $c->order->total_price;
        });

        $totalPendapatanBersih = $totalPendapatanKotor - $totalRefund;

        return view('pesanan.laporan', compact(
            'orders',
            'claims',
            'totalPendapatanKotor',
            'totalRefund',
            'totalPendapatanBersih',
            'period'
        ));
    }

   /**
    * Riwayat transaksi dengan detail pembayaran Midtrans (untuk admin/owner).
    * View: transaksi/index.blade.php (akan dibuat di Tahap 10)
    * Route: GET /riwayat-transaksi (akan ditambahkan di Tahap 9)
    *
    * Mendukung filter opsional via query string:
    *   ?payment_status=settlement → tampilkan yang sudah lunas
    *   ?order_status=diproses    → tampilkan yang sedang diproses
    */
    public function riwayat(Request $request): View
    {
        $query = Order::with('user')
            ->whereIn('order_status', [
                \App\Enums\OrderStatus::SELESAI,
                \App\Enums\OrderStatus::DIBATALKAN
            ])
            ->latest();

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('pesanan.riwayat', compact('orders'));
    }

    /**
     * Export laporan penjualan ke format Excel (XLS).
     */
    public function laporanExport(Request $request)
    {
        $period = $request->input('period', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Order::with(['user', 'claim'])
            ->where('order_status', \App\Enums\OrderStatus::SELESAI);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            $dateLimit = null;
            if ($period === '1_month') {
                $dateLimit = now()->subMonth();
            } elseif ($period === '3_months') {
                $dateLimit = now()->subMonths(3);
            } elseif ($period === '6_months') {
                $dateLimit = now()->subMonths(6);
            } elseif ($period === '1_year') {
                $dateLimit = now()->subYear();
            }

            if ($dateLimit) {
                $query->where('created_at', '>=', $dateLimit);
            }
        }

        $orders = $query->latest()->get();

        $totalPendapatanKotor = $orders->sum('total_price');

        // Fetch claims related to the filtered orders
        $claims = \App\Models\OrderClaim::with(['order.user'])
            ->whereIn('order_id', $orders->pluck('id'))
            ->latest()
            ->get();

        // Calculate total refund from approved claims
        $totalRefund = $claims->where('status', 'approved')->sum(function($c) {
            return $c->order->total_price;
        });

        $totalPendapatanBersih = $totalPendapatanKotor - $totalRefund;

        $html = view('pesanan.laporan_excel', compact(
            'orders',
            'claims',
            'totalPendapatanKotor',
            'totalRefund',
            'totalPendapatanBersih',
            'period'
        ))->render();

        $filename = "Laporan_Penjualan_" . $period . "_" . date('Y-m-d') . ".xls";

        $headers = [
            "Content-type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response($html, 200, $headers);
    }

    /**
     * Cetak laporan penjualan ke format PDF (DomPDF).
     */
    public function laporanPdf(Request $request)
    {
        $period = $request->input('period', 'all');

        $query = Order::with(['user', 'claim'])
            ->where('order_status', \App\Enums\OrderStatus::SELESAI);

        $dateLimit = null;
        if ($period === '1_month') {
            $dateLimit = now()->subMonth();
        } elseif ($period === '3_months') {
            $dateLimit = now()->subMonths(3);
        } elseif ($period === '6_months') {
            $dateLimit = now()->subMonths(6);
        } elseif ($period === '1_year') {
            $dateLimit = now()->subYear();
        }

        if ($dateLimit) {
            $query->where('created_at', '>=', $dateLimit);
        }

        $orders = $query->latest()->get();

        $totalPendapatanKotor = $orders->sum('total_price');

        // Fetch claims related to the filtered orders
        $claims = \App\Models\OrderClaim::with(['order.user'])
            ->whereIn('order_id', $orders->pluck('id'))
            ->latest()
            ->get();

        // Calculate total refund from approved claims
        $totalRefund = $claims->where('status', 'approved')->sum(function($c) {
            return $c->order->total_price;
        });

        $totalPendapatanBersih = $totalPendapatanKotor - $totalRefund;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pesanan.laporan_pdf', compact(
            'orders',
            'claims',
            'totalPendapatanKotor',
            'totalRefund',
            'totalPendapatanBersih',
            'period'
        ));

        return $pdf->stream("Laporan_Penjualan_" . $period . "_" . date('Y-m-d') . ".pdf");
    }

   /**
    * Pelanggan menandai pesanan telah diterima (sukses/selesai).
    */
   public function terima(Order $pesanan): RedirectResponse
   {
       // Pastikan hanya pemilik pesanan yang bisa mengonfirmasi
       if ($pesanan->user_id !== Auth::id()) {
           abort(403, 'Aksi tidak diizinkan.');
       }

       // Update status order ke selesai
       $pesanan->update([
           'order_status' => 'selesai',
           'status' => 'paid', // legacy compatibility
       ]);

       // Catat di shipment tracking jika ada shipment
       if ($pesanan->shipment) {
           $pesanan->shipment->update([
               'status' => 'Delivered'
           ]);
           
           $trackingService = app(\App\Services\TrackingService::class);
           $trackingService->updateStatus(
               $pesanan->shipment,
               'Delivered',
               'Pesanan telah diterima oleh pelanggan.',
               null
           );
       }

       // Catat Log Aktivitas
       \App\Models\ActivityLog::create([
           'user_id' => Auth::id(),
           'activity' => 'Konfirmasi Pesanan Diterima',
           'description' => "Pelanggan menandai pesanan #{$pesanan->invoice_number} telah diterima.",
           'ip_address' => request()->ip(),
           'user_agent' => request()->userAgent(),
       ]);

       return redirect()->back()->with('success', 'Terima kasih! Pesanan Anda telah selesai.');
   }

   /**
    * Trigger status transition to 'sedang_dicetak' when admin views/downloads design file
    * if the product requires design file.
    */
   public function triggerPrint(Order $pesanan): \Illuminate\Http\JsonResponse
   {
       $hasCetak = $pesanan->orderDetails()->whereHas('product', function($q) {
           $q->where('requires_design_file', true);
       })->exists();

       if ($hasCetak && ($pesanan->order_status === \App\Enums\OrderStatus::DIPROSES || $pesanan->order_status === \App\Enums\OrderStatus::PAID)) {
           if ($pesanan->order_status === \App\Enums\OrderStatus::PAID) {
               $pesanan->update(['order_status' => \App\Enums\OrderStatus::DIPROSES]);
           }
           $updated = $this->orderWorkflowService->startPrinting($pesanan);

           return response()->json([
               'success' => true,
               'updated' => $updated,
               'message' => 'Status otomatis diubah menjadi Sedang Dicetak.'
           ]);
       }

        return response()->json([
            'success' => true,
            'updated' => false,
            'message' => 'Status tidak berubah.'
        ]);
    }

    /**
     * Batalkan pesanan oleh customer atau admin.
     * Aturan:
     *   - Customer hanya bisa membatalkan jika pembayaran masih Pending
     *   - Mengembalikan stok produk
     *   - Mengirim notifikasi ke admin/owner
     */
    public function destroy(Order $pesanan): RedirectResponse
    {
        $user = Auth::user();

        // Customer hanya bisa membatalkan pesanan miliknya sendiri
        if ($user->role === 'customer' && $pesanan->user_id !== $user->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        // Customer hanya bisa membatalkan jika status pembayaran pending
        if ($user->role === 'customer' && $pesanan->payment_status !== 'pending') {
            return back()->with('error', 'Anda tidak dapat membatalkan pesanan yang sudah dibayar.');
        }

        try {
            // Gunakan workflow service untuk cancel & kembalikan stok
            $this->orderWorkflowService->cancel($pesanan);

            // Kirim notifikasi ke admin dan owner
            $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AppNotification(
                    'Pesanan Dibatalkan Pelanggan',
                    "Pesanan #{$pesanan->invoice_number} telah dibatalkan oleh customer {$pesanan->user->name}.",
                    'x-circle',
                    'red',
                    route('transaksi.riwayat'),
                    'pesanan'
                ));
            }

            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Customer track package page.
     */
    public function trackCustomer(Order $order)
    {
        // Pastikan order milik customer yang login
        if ($order->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $order->load(['shipment.trackings']);
        $shipment = $order->shipment;
        
        // Menentukan status timeline progress
        $timeline = [
            'Order Dibuat' => [
                'active' => true,
                'time' => $order->created_at,
                'desc' => 'Pesanan berhasil dibuat.'
            ],
            'Pembayaran Berhasil' => [
                'active' => in_array($order->payment_status, ['paid', 'settlement', 'capture']),
                'time' => $order->paid_at ?? ($order->payment_status === 'settlement' ? $order->updated_at : null),
                'desc' => 'Pembayaran lunas terverifikasi.'
            ],
            'Sedang Dicetak' => [
                'active' => in_array($order->order_status?->value ?? $order->order_status, ['sedang_dicetak', 'siap_dikemas', 'dikemas', 'dikirim', 'selesai']),
                'time' => null,
                'desc' => 'Pesanan sedang diproduksi di workshop.'
            ],
            'Siap Dikirim' => [
                'active' => in_array($order->order_status?->value ?? $order->order_status, ['siap_dikemas', 'dikemas', 'dikirim', 'selesai']),
                'time' => null,
                'desc' => 'Produksi selesai, menunggu pengambilan kurir.'
            ],
            'Paket Dijemput' => [
                'active' => $shipment && (in_array($shipment->pickup_status, ['picked_up', 'picked_up_by_courier']) || in_array($order->order_status?->value ?? $order->order_status, ['dikirim', 'selesai'])),
                'time' => $shipment?->updated_at,
                'desc' => 'Paket telah diserahterimakan ke kurir.'
            ],
            'Dalam Pengiriman' => [
                'active' => in_array($order->order_status?->value ?? $order->order_status, ['dikirim', 'selesai']),
                'time' => null,
                'desc' => 'Paket sedang dikirim oleh kurir ke alamat tujuan.'
            ],
            'Terkirim' => [
                'active' => ($shipment && strtolower($shipment->status) === 'delivered') || ($order->order_status?->value ?? $order->order_status) === 'selesai',
                'time' => null,
                'desc' => 'Paket telah sampai di alamat tujuan.'
            ],
            'Selesai' => [
                'active' => ($order->order_status?->value ?? $order->order_status) === 'selesai',
                'time' => $order->updated_at,
                'desc' => 'Transaksi selesai.'
            ]
        ];

        return view('pesanan.track', compact('order', 'shipment', 'timeline'));
    }
}