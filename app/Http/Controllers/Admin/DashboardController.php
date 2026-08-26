<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Admin/Owner dengan data BI real-time.
     * Mendukung filter tanggal dan AJAX response untuk live update.
     */
    public function index(Request $request)
    {
        Order::autoCompleteDeliveredOrders();
        $user = Auth::user();

        // ──────────────────────────────────────────────────
        // Jika customer — kembalikan ke route dashboard customer
        // ──────────────────────────────────────────────────
        if ($user->role === 'customer') {
            return $this->customerDashboard($request, $user);
        }

        // ──────────────────────────────────────────────────
        // ADMIN / OWNER: Filter waktu
        // ──────────────────────────────────────────────────
        $filter    = $request->query('filter', 'month');
        $startDate = null;
        $endDate   = null;

        match ($filter) {
            'today'  => [$startDate, $endDate] = [now()->startOfDay(),   now()->endOfDay()],
            '7days'  => [$startDate, $endDate] = [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30days' => [$startDate, $endDate] = [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'month'  => [$startDate, $endDate] = [now()->startOfMonth(), now()->endOfMonth()],
            'year'   => [$startDate, $endDate] = [now()->startOfYear(),  now()->endOfYear()],
            'custom' => [$startDate, $endDate] = [
                $request->query('start_date') ? now()->parse($request->query('start_date'))->startOfDay() : now()->startOfMonth(),
                $request->query('end_date')   ? now()->parse($request->query('end_date'))->endOfDay()     : now()->endOfMonth(),
            ],
            default  => [$startDate, $endDate] = [now()->startOfMonth(), now()->endOfMonth()],
        };

        // 1. KPI COUNTERS
        // ──────────────────────────────────────────────────
        $pendingCount = Order::where('order_status', \App\Enums\OrderStatus::PENDING)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $prosesCount = Order::whereIn('order_status', [\App\Enums\OrderStatus::DIPROSES, \App\Enums\OrderStatus::PAID])
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $sedangDicetakCount = Order::where('order_status', \App\Enums\OrderStatus::SEDANG_DICETAK)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $siapDikemasCount = Order::where('order_status', \App\Enums\OrderStatus::SIAP_DIKEMAS)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $dikemasCount = Order::where('order_status', \App\Enums\OrderStatus::DIKEMAS)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $dikirimCount = Order::where('order_status', \App\Enums\OrderStatus::DIKIRIM)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $selesaiCount = Order::where('order_status', \App\Enums\OrderStatus::SELESAI)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $totalOrdersCount = Order::when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $totalCustomersCount = User::where('role', 'customer')
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->count();

        $totalRevenue = Order::where('order_status', \App\Enums\OrderStatus::SELESAI)
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('total_price');

        $totalProduk  = Product::count();
        $stokMenipis  = Product::where('stock', '<', 10)->where('stock', '>', 0)->count();
        $stokHabis    = Product::where('stock', '=', 0)->count();
        $criticalProducts = Product::with('category')
            ->where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->get();

        // ──────────────────────────────────────────────────
        // 2. DATA CHART 1 & 3: Pendapatan & Order Bulanan (6 bulan terakhir)
        // ──────────────────────────────────────────────────
        $chartMonths = [];
        $chartSales  = [];
        $chartOrders = [];

        for ($i = 5; $i >= 0; $i--) {
            $date          = now()->subMonths($i);
            $chartMonths[] = $date->translatedFormat('F Y');

            $monthlyData = Order::where('order_status', \App\Enums\OrderStatus::SELESAI)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->select(
                    DB::raw('SUM(total_price) as total_sales'),
                    DB::raw('COUNT(id) as total_orders')
                )
                ->first();

            $chartSales[]  = (int) ($monthlyData->total_sales  ?? 0);
            $chartOrders[] = (int) ($monthlyData->total_orders ?? 0);
        }

        // ──────────────────────────────────────────────────
        // 3. DATA CHART 2: Status Pesanan (Doughnut)
        // ──────────────────────────────────────────────────
        $statusCounts = Order::select('order_status', DB::raw('count(*) as qty'))
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('order_status')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->order_status instanceof \App\Enums\OrderStatus 
                    ? $item->order_status->value 
                    : (string) $item->order_status;
                return [$key => $item->qty];
            })
            ->toArray();

        $chartStatusLabels = ['Pending', 'Diproses', 'Selesai', 'Dibatalkan'];
        $chartStatusValues = [
            (int) ($statusCounts['pending']    ?? 0),
            (int) ($statusCounts['diproses']   ?? 0) + 
            (int) ($statusCounts['paid']       ?? 0) + 
            (int) ($statusCounts['sedang_dicetak'] ?? 0) + 
            (int) ($statusCounts['siap_dikemas']   ?? 0) + 
            (int) ($statusCounts['dikemas']    ?? 0) + 
            (int) ($statusCounts['dikirim']    ?? 0),
            (int) ($statusCounts['selesai']    ?? 0),
            (int) ($statusCounts['dibatalkan'] ?? 0),
        ];

        // ──────────────────────────────────────────────────
        // 4. DATA CHART 4: Produk Terlaris Top 5 (Pie)
        // ──────────────────────────────────────────────────
        $topProducts = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.product_name',
                DB::raw('SUM(order_details.qty) as total_qty')
            )
            ->where('orders.order_status', 'selesai')
            ->when($startDate, fn($q) => $q->whereBetween('orders.created_at', [$startDate, $endDate]))
            ->groupBy('products.id', 'products.product_name')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        $topProductLabels = $topProducts->pluck('product_name')->toArray();
        $topProductValues = $topProducts->pluck('total_qty')->map(fn($v) => (int) $v)->toArray();
        $topProductIds    = $topProducts->pluck('product_id')->toArray();

        // ──────────────────────────────────────────────────
        // 5. DATA CHART 5: Customer Baru (Line — 6 bulan terakhir)
        // ──────────────────────────────────────────────────
        $customerGrowthMonths = [];
        $customerGrowthValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $date                   = now()->subMonths($i);
            $customerGrowthMonths[] = $date->translatedFormat('F Y');
            $customerGrowthValues[] = User::where('role', 'customer')
                ->whereYear('created_at',  $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        // ──────────────────────────────────────────────────
        // 6. DATA CHART 6: Penjualan per Kategori (Horizontal Bar)
        // ──────────────────────────────────────────────────
        $categorySales = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.category_name',
                DB::raw('SUM(order_details.qty) as total_qty')
            )
            ->where('orders.order_status', 'selesai')
            ->when($startDate, fn($q) => $q->whereBetween('orders.created_at', [$startDate, $endDate]))
            ->groupBy('categories.id', 'categories.category_name')
            ->orderBy('total_qty', 'desc')
            ->get();

        $categoryLabels = $categorySales->pluck('category_name')->toArray();
        $categoryValues = $categorySales->pluck('total_qty')->map(fn($v) => (int) $v)->toArray();
        $categoryIds    = $categorySales->pluck('category_id')->toArray();

        // ──────────────────────────────────────────────────
        // 7. RECENT ORDERS (10 terbaru)
        // ──────────────────────────────────────────────────
        $recentOrdersList = Order::with('user')
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->latest()
            ->take(10)
            ->get();

        // ──────────────────────────────────────────────────
        // 8. ACTIVITY LOGS (10 terbaru)
        // ──────────────────────────────────────────────────
        $activityLogsList = ActivityLog::with('user')
            ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->latest()
            ->take(10)
            ->get();

        // ──────────────────────────────────────────────────
        // AJAX RESPONSE (filter tanpa reload halaman)
        // ──────────────────────────────────────────────────
        if ($request->ajax() || $request->query('ajax')) {
            return response()->json([
                'kpi' => [
                    'pending'                => $pendingCount,
                    'processing'             => $prosesCount,
                    'sedang_dicetak'         => $sedangDicetakCount,
                    'siap_dikemas'           => $siapDikemasCount,
                    'dikemas'                => $dikemasCount,
                    'dikirim'                => $dikirimCount,
                    'done'                   => $selesaiCount,
                    'total_orders'           => $totalOrdersCount,
                    'total_customers'        => $totalCustomersCount,
                    'total_revenue'          => $totalRevenue,
                    'total_revenue_formatted'=> 'Rp' . number_format($totalRevenue, 0, ',', '.'),
                    'total_products'         => $totalProduk,
                    'stock_menipis'          => $stokMenipis,
                    'stock_habis'            => $stokHabis,
                ],
                'charts' => [
                    'revenue'      => ['labels' => $chartMonths,        'values' => $chartSales],
                    'orders'       => ['labels' => $chartMonths,        'values' => $chartOrders],
                    'status'       => ['labels' => $chartStatusLabels,  'values' => $chartStatusValues],
                    'top_products' => ['labels' => $topProductLabels,   'values' => $topProductValues, 'ids' => $topProductIds],
                    'growth'       => ['labels' => $customerGrowthMonths, 'values' => $customerGrowthValues],
                    'categories'   => ['labels' => $categoryLabels,     'values' => $categoryValues,   'ids' => $categoryIds],
                ],
                'recent_orders' => $recentOrdersList->map(fn($o) => [
                    'id'              => $o->id,
                    'invoice'         => $o->invoice_number,
                    'customer'        => $o->user?->name ?? 'Tamu',
                    'customer_id'     => $o->user_id,
                    'date'            => $o->created_at->format('d M Y H:i'),
                    'status'          => $o->order_status instanceof \App\Enums\OrderStatus ? $o->order_status->value : $o->order_status,
                    'status_label'    => $o->order_status_label,
                    'status_badge'    => $o->order_status_badge_class,
                    'total'           => $o->total_price,
                    'total_formatted' => 'Rp' . number_format($o->total_price, 0, ',', '.'),
                ]),
                'logs' => $activityLogsList->map(fn($l) => [
                    'id'          => $l->id,
                    'user'        => $l->user?->name ?? 'Sistem',
                    'activity'    => $l->activity,
                    'description' => $l->description,
                    'date'        => $l->created_at->format('d M Y H:i'),
                    'ip'          => $l->ip_address,
                    'user_agent'  => $l->user_agent,
                ]),
            ]);
        }

        // ──────────────────────────────────────────────────
        // VIEW RESPONSE (halaman penuh)
        // ──────────────────────────────────────────────────
        return view('admin.dashboard', compact(
            'pendingCount', 'prosesCount', 'sedangDicetakCount', 'siapDikemasCount', 'dikemasCount', 'dikirimCount', 'selesaiCount',
            'totalOrdersCount', 'totalCustomersCount', 'totalRevenue', 'totalProduk',
            'stokMenipis', 'stokHabis', 'criticalProducts',
            'chartMonths', 'chartSales', 'chartOrders',
            'topProductLabels', 'topProductValues', 'topProductIds',
            'customerGrowthMonths', 'customerGrowthValues',
            'chartStatusLabels', 'chartStatusValues',
            'categoryLabels', 'categoryValues', 'categoryIds',
            'recentOrdersList', 'activityLogsList'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // CUSTOMER DASHBOARD (private helper)
    // ──────────────────────────────────────────────────────────────────
    private function customerDashboard(Request $request, $user)
    {
        Order::autoCompleteDeliveredOrders();
        $myOrders = Order::where('user_id', $user->id)
            ->with(['orderDetails.product.category', 'reviews', 'shipment.trackings'])
            ->latest()
            ->get();

        return view('dashboard', compact('myOrders'));
    }
}
