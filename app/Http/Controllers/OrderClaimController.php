<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderClaimController extends Controller
{
    /**
     * Tampilkan formulir pengajuan klaim garansi oleh pelanggan.
     */
    public function create(Order $order): View|RedirectResponse
    {
        // Pastikan order milik user yang login
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan mengakses pesanan ini.');
        }

        // Pastikan status order adalah selesai
        $orderStatus = $order->order_status?->value ?? $order->order_status;
        if ($orderStatus !== 'selesai') {
            return redirect()->route('dashboard')->with('error', 'Klaim garansi hanya dapat diajukan untuk pesanan yang telah selesai/diterima.');
        }

        // Pastikan belum ada klaim terdaftar
        if ($order->claim) {
            return redirect()->route('dashboard')->with('error', 'Anda telah mengajukan klaim garansi untuk pesanan ini sebelumnya.');
        }

        return view('pesanan.claim', compact('order'));
    }

    /**
     * Simpan pengajuan klaim garansi oleh pelanggan.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak diizinkan mengakses pesanan ini.');
        }

        $orderStatus = $order->order_status?->value ?? $order->order_status;
        if ($orderStatus !== 'selesai') {
            return redirect()->route('dashboard')->with('error', 'Klaim garansi hanya dapat diajukan untuk pesanan yang telah selesai/diterima.');
        }

        if ($order->claim) {
            return redirect()->route('dashboard')->with('error', 'Anda telah mengajukan klaim garansi untuk pesanan ini sebelumnya.');
        }

        $request->validate([
            'reason'              => 'required|string|in:Produk Rusak,Produk Tidak Sesuai,Lainnya',
            'description'         => 'required|string|min:10|max:2000',
            'proof_video'         => 'required|file|mimes:mp4,mov,avi,mkv,3gp|max:51200',
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name'   => 'required|string|max:150',
        ], [
            'reason.required'              => 'Alasan klaim wajib dipilih.',
            'description.required'         => 'Penjelasan detail wajib diisi.',
            'description.min'              => 'Penjelasan detail minimal 10 karakter.',
            'proof_video.required'         => 'Video unboxing bukti kerusakan wajib diunggah.',
            'proof_video.file'             => 'File bukti harus berupa video.',
            'proof_video.mimes'            => 'Format video harus berupa mp4, mov, avi, mkv, atau 3gp.',
            'proof_video.max'              => 'Ukuran video unboxing maksimal adalah 50MB.',
            'bank_name.required'           => 'Nama bank atau e-wallet wajib diisi.',
            'bank_account_number.required' => 'Nomor rekening atau nomor e-wallet wajib diisi.',
            'bank_account_name.required'   => 'Nama pemilik rekening/akun wajib diisi.',
        ]);

        // Upload video bukti
        $proofPath = null;
        if ($request->hasFile('proof_video')) {
            $proofPath = $request->file('proof_video')->store('claims', 'public');
        }

        // Create Claim
        $claim = OrderClaim::create([
            'order_id'            => $order->id,
            'reason'              => $request->reason,
            'description'         => $request->description,
            'proof_video'         => $proofPath,
            'status'              => 'pending',
            'bank_name'           => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name'   => $request->bank_account_name,
        ]);

        // Kirim notifikasi ke Admin
        $admins = User::whereIn('role', ['admin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AppNotification(
                'Pengajuan Garansi Baru',
                "Pelanggan {$order->user->name} mengajukan klaim garansi untuk pesanan #{$order->invoice_number}.",
                'alert-triangle',
                'red',
                route('admin.claims.show', $claim->id),
                'komplain'
            ));
        }

        return redirect()->route('dashboard', ['tab' => 'pesanan'])->with('success', 'Klaim garansi uang kembali berhasil diajukan. Mohon tunggu verifikasi Admin.');
    }

    /**
     * Tampilkan daftar klaim untuk Admin/Owner.
     */
    public function adminIndex(): View
    {
        $claims = OrderClaim::with(['order.user'])
            ->latest()
            ->get();

        return view('admin.claims.index', compact('claims'));
    }

    /**
     * Tampilkan detail klaim untuk Admin/Owner.
     */
    public function adminShow(OrderClaim $claim): View
    {
        $claim->load(['order.user', 'order.orderDetails.product']);
        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Update status verifikasi klaim (Approve / Reject) oleh Admin/Owner.
     */
    public function adminUpdateStatus(Request $request, OrderClaim $claim): RedirectResponse
    {
        $request->validate([
            'status'      => 'required|string|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $claim->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        // Mengirim notifikasi ke Pelanggan mengenai keputusan klaim
        $customer = $claim->order->user;
        if ($customer) {
            $statusLabel = $request->status === 'approved' ? 'DISETUJUI' : 'DITOLAK';
            $color = $request->status === 'approved' ? 'green' : 'red';
            $icon = $request->status === 'approved' ? 'check-circle' : 'x-circle';

            $customer->notify(new \App\Notifications\AppNotification(
                "Garansi Komplain {$statusLabel}",
                "Pengajuan garansi Anda untuk pesanan #{$claim->order->invoice_number} telah {$statusLabel} oleh Admin.",
                $icon,
                $color,
                route('dashboard') . '?tab=pesanan',
                'pesanan'
            ));
        }

        return redirect()->route('admin.claims.index')->with('success', 'Keputusan verifikasi klaim garansi berhasil disimpan.');
    }
}
