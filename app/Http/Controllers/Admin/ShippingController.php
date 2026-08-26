<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ActivityLog;
use App\Services\BiteshipService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use App\Services\LabelService;
use App\Services\OrderWorkflowService;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function __construct(
        protected BiteshipService $biteship,
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService,
        protected LabelService $labelService,
        protected OrderWorkflowService $orderWorkflowService
    ) {}

    /**
     * Tampilkan daftar pengiriman/order yang berhak dikirim.
     */
    public function index()
    {
        $orders = Order::with(['user', 'shipment.trackings'])
            ->whereIn('payment_status', ['settlement', 'capture', 'paid'])
            ->whereIn('order_status', [
                OrderStatus::SIAP_DIKEMAS,
                OrderStatus::DIKEMAS,
                OrderStatus::DIKIRIM,
                OrderStatus::SELESAI,
                OrderStatus::SIAP_DIAMBIL
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.shipping.index', compact('orders'));
    }

    /**
     * Tampilkan halaman Pengaturan Pengiriman.
     */
    public function settingsIndex()
    {
        $settings = [
            'biteship_api_key' => Setting::getVal('biteship_api_key') ?: config('biteship.api_key'),
            'biteship_base_url' => Setting::getVal('biteship_base_url') ?: config('biteship.base_url', 'https://api.biteship.com'),
            'biteship_origin_id' => Setting::getVal('biteship_origin_id') ?: config('biteship.origin_id'),
        ];

        return view('admin.shipping.settings', compact('settings'));
    }

    /**
     * Simpan Pengaturan Pengiriman.
     */
    public function settingsUpdate(Request $request)
    {
        $request->validate([
            'biteship_api_key' => 'nullable|string|max:255',
            'biteship_base_url' => 'required|url',
            'biteship_origin_id' => 'nullable|string|max:255',
        ]);

        Setting::setVal('biteship_api_key', $request->biteship_api_key, 'Biteship API Key');
        Setting::setVal('biteship_base_url', $request->biteship_base_url, 'Biteship Base URL');
        Setting::setVal('biteship_origin_id', $request->biteship_origin_id, 'Biteship Origin Area ID');

        return redirect()->route('admin.shipping_settings.index')->with('success', 'Pengaturan Pengiriman berhasil diperbarui!');
    }

    /**
     * Test koneksi ke API Biteship.
     */
    public function testConnection(Request $request)
    {
        $apiKey = $request->biteship_api_key ?: Setting::getVal('biteship_api_key') ?: config('biteship.api_key');
        $baseUrl = $request->biteship_base_url ?: Setting::getVal('biteship_base_url') ?: config('biteship.base_url', 'https://api.biteship.com');

        if (empty($apiKey)) {
            return redirect()->back()->with('error', 'Gagal: API Key belum diisi.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json'
            ])->get("{$baseUrl}/v1/couriers");

            if ($response->successful()) {
                return redirect()->back()->with('success', '✅ Connected to Biteship! Koneksi berhasil dan API Key aktif.');
            }

            $body = $response->json();
            $errMsg = $body['message'] ?? $response->body() ?? 'Gagal menghubungi Biteship.';
            return redirect()->back()->with('error', "❌ Connection failed (HTTP {$response->status()}): {$errMsg}");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Connection failed: ' . $e->getMessage());
        }
    }

    public function createShipment(Order $order, \Illuminate\Http\Request $request)
    {
        try {
            if ($order->shipping_courier === 'pickup') {
                $order->update([
                    'order_status' => \App\Enums\OrderStatus::SIAP_DIAMBIL,
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Siap Diambil',
                    'description' => "Admin menandai pesanan #{$order->invoice_number} siap diambil di toko.",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->back()->with('success', 'Status pesanan berhasil diubah menjadi Siap Diambil!');
            }

            // Create shipment records (API or dummy fallback inside service)
            $shipment = $this->shipmentService->createShipmentForOrder($order);
            
            // Transition state via workflow service
            $this->orderWorkflowService->pack($order, $shipment->tracking_number, $shipment->shipment_id, $shipment->status);

            if ($request->query('action') === 'print') {
                return redirect()->route('admin.shipping.shipments.label', $shipment->id);
            }

            return redirect()->back()->with('success', "Resi {$shipment->tracking_number} berhasil dibuat dan status pesanan otomatis diubah menjadi Dikemas!");
        } catch (\Exception $e) {
            Log::error('Create shipment route error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Simulate courier pickup status.
     */
    public function simulatePickup(Request $request, Shipment $shipment)
    {
        $request->validate([
            'pickup_status' => 'required|in:waiting_pickup,pickup_requested,picked_up',
        ]);

        try {
            $shipment->update([
                'pickup_status' => $request->pickup_status,
            ]);

            $statusTextMap = [
                'pickup_requested' => 'Pickup Requested',
                'picked_up' => 'Picked Up',
                'waiting_pickup' => 'Waiting Pickup',
            ];

            $descriptionMap = [
                'pickup_requested' => 'Permintaan penjemputan paket telah dikirim ke kurir.',
                'picked_up' => 'Paket telah berhasil diserahkan ke kurir dan sedang menuju fasilitas sortir.',
                'waiting_pickup' => 'Menunggu kurir melakukan penjemputan paket.',
            ];

            $this->trackingService->updateStatus(
                $shipment,
                $statusTextMap[$request->pickup_status],
                $descriptionMap[$request->pickup_status]
            );

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Simulasi Pickup',
                'description' => "Admin mengubah status pickup menjadi {$request->pickup_status} untuk resi {$shipment->tracking_number}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->back()->with('success', 'Status pickup berhasil diubah!');
        } catch (\Exception $e) {
            Log::error('Simulate pickup error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal melakukan simulasi pickup: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan pengemasan dan tandai paket dikirim.
     */
    public function kirimPaket(Shipment $shipment)
    {
        try {
            // Transition state via workflow service
            $this->orderWorkflowService->ship($shipment->order);

            return redirect()->back()->with('success', 'Paket berhasil ditandai sebagai Dikirim dan pelacakan dimulai!');
        } catch (\Exception $e) {
            Log::error('Kirim paket error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Admin menyelesaikan pesanan secara manual (jika customer belum klik).
     */
    public function selesaikanPaket(Shipment $shipment)
    {
        try {
            // Transition state via workflow service
            $this->orderWorkflowService->complete($shipment->order, 'Admin');

            return redirect()->back()->with('success', 'Pesanan berhasil diselesaikan!');
        } catch (\Exception $e) {
            Log::error('Selesaikan paket error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyelesaikan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Simulate shipping tracking status.
     */
    public function updateTracking(Request $request, Shipment $shipment)
    {
        $request->validate([
            'status' => 'required|in:Picked Up,Sorting Facility,In Transit,Out For Delivery,Delivered',
            'description' => 'required|string|max:500',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $this->trackingService->updateStatus(
                $shipment,
                $request->status,
                $request->description,
                $request->location
            );

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Update Tracking Pengiriman',
                'description' => "Admin menambahkan tracking status '{$request->status}' untuk resi {$shipment->tracking_number}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->back()->with('success', 'Status pelacakan berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Update tracking error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui status tracking: ' . $e->getMessage());
        }
    }

    /**
     * Download or stream shipping label PDF (Admin).
     */
    public function downloadLabel(Shipment $shipment)
    {
        try {
            $data = $this->labelService->getLabelData($shipment);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.shipping.label', $data);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            return $pdf->stream("Label-".$shipment->tracking_number.".pdf");
        } catch (\Exception $e) {
            Log::error('Download label error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat file PDF label: ' . $e->getMessage());
        }
    }

    /**
     * Download or stream shipping label PDF (Customer).
     */
    public function downloadLabelCustomer(Shipment $shipment)
    {
        // Authorization check: ensure order belongs to logged-in user or role is admin/owner
        if ($shipment->order->user_id !== Auth::id() && !in_array(Auth::user()->role, ['admin', 'owner'])) {
            abort(403, 'Anda tidak diizinkan mendownload label pesanan ini.');
        }

        try {
            $data = $this->labelService->getLabelData($shipment);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.shipping.label', $data);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            return $pdf->stream("Label-".$shipment->tracking_number.".pdf");
        } catch (\Exception $e) {
            Log::error('Download label customer error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat file PDF label: ' . $e->getMessage());
        }
    }

    /**
     * Update Nomor Resi dan Status Pengiriman secara manual (untuk testing / backward compatibility).
     */
    public function updateResi(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        try {
            $shipment = $order->shipment;
            if (!$shipment) {
                $shipment = \App\Models\Shipment::create([
                    'order_id' => $order->id,
                    'tracking_number' => $request->tracking_number,
                    'courier' => $order->shipping_courier ?: 'jne',
                    'service' => $order->shipping_service ?: 'reg',
                    'status' => 'allocated',
                    'pickup_status' => 'waiting_pickup',
                ]);
            } else {
                $shipment->update([
                    'tracking_number' => $request->tracking_number,
                ]);
            }

            $order->update([
                'tracking_number' => $request->tracking_number,
                'shipping_status' => 'dikirim',
                'order_status' => \App\Enums\OrderStatus::DIKIRIM,
                'status' => 'paid',
            ]);

            return redirect()->back()->with('success', 'Nomor resi berhasil diperbarui dan status pesanan diubah menjadi Dikirim!');
        } catch (\Exception $e) {
            Log::error('Update resi failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui nomor resi pengiriman.');
        }
    }
}
