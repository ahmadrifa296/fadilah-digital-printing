<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Verifikasi Klaim Garansi #{{ $claim->order->invoice_number }}</h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Navigation Back -->
        <div>
            <a href="{{ route('admin.claims.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-orange-500 transition-colors uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Klaim Garansi
            </a>
        </div>

        <!-- Header Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md bg-orange-100 text-orange-700 font-extrabold text-[10px] uppercase tracking-wider">Pemeriksaan Garansi</span>
                    <span class="text-xs font-mono font-bold text-slate-400">#{{ $claim->order->invoice_number }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Verifikasi Klaim Garansi</h1>
                <p class="text-xs text-slate-500 mt-1">Diajukan oleh <strong class="text-slate-800">{{ $claim->order->user->name ?? 'Tamu' }}</strong> &bull; {{ $claim->created_at->format('d F Y H:i') }}</p>
            </div>
            <div class="shrink-0">
                <span class="px-4 py-2 rounded-full text-xs font-black border uppercase tracking-wider inline-flex items-center gap-1.5 shadow-2xs
                    {{ $claim->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                    {{ $claim->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                    {{ $claim->status === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                ">
                    {{ $claim->status === 'pending' ? '⏳ Status: Pending' : ($claim->status === 'approved' ? '✅ Status: Disetujui' : '❌ Status: Ditolak') }}
                </span>
            </div>
        </div>

        <!-- Grid Layout 2 Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2 Columns: Evidence & Claims Detail -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Video Unboxing Player -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="text-base">📹</span> Bukti Video Unboxing Pelanggan
                        </h2>
                        @if($claim->proof_video)
                            <a href="{{ asset('storage/' . $claim->proof_video) }}" target="_blank" class="text-[10px] text-orange-500 hover:text-orange-600 font-extrabold uppercase tracking-wider inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Tab Baru
                            </a>
                        @endif
                    </div>
                    
                    @if($claim->proof_video)
                        <div class="border border-slate-300 rounded-2xl overflow-hidden bg-black shadow-inner">
                            <video src="{{ asset('storage/' . $claim->proof_video) }}" controls class="w-full h-auto max-h-[420px] block mx-auto"></video>
                        </div>
                    @else
                        <div class="p-10 text-center bg-slate-50 border border-dashed border-slate-200 text-slate-400 rounded-2xl italic text-xs">
                            Pelanggan tidak mengunggah video unboxing.
                        </div>
                    @endif
                </div>

                <!-- Keluhan Detail -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Rincian Keluhan & Deskripsi Masalah
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="bg-rose-50/60 border border-rose-200/60 p-3.5 rounded-2xl">
                            <span class="block text-[9px] font-bold text-rose-500 uppercase tracking-wider">Kategori Alasan</span>
                            <span class="font-black text-rose-700 text-sm mt-0.5 block">{{ $claim->reason }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/60 p-3.5 rounded-2xl">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Waktu Waktu Pengajuan</span>
                            <span class="font-bold text-slate-800 mt-0.5 block">{{ $claim->created_at->format('d F Y H:i:s') }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Penjelasan Pelanggan</span>
                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl text-xs text-slate-800 leading-relaxed font-medium">
                            {{ $claim->description }}
                        </div>
                    </div>
                </div>

                <!-- Rekening Refund Card -->
                <div class="bg-gradient-to-br from-white to-orange-50/20 rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                        <span>💳</span> Data Rekening Pengembalian Dana (Refund)
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Bank / E-Wallet</span>
                            <span class="text-slate-900 text-xs font-black uppercase">{{ $claim->bank_name ?? '-' }}</span>
                        </div>
                        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">No. Rekening / HP</span>
                            <span class="text-slate-900 text-xs font-black font-mono select-all">{{ $claim->bank_account_number ?? '-' }}</span>
                        </div>
                        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Atas Nama</span>
                            <span class="text-slate-900 text-xs font-black">{{ $claim->bank_account_name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Barang Yang Dipesan -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Produk Dalam Pesanan #{{ $claim->order->invoice_number }}
                    </h2>
                    
                    <div class="divide-y divide-slate-100">
                        @foreach($claim->order->orderDetails as $detail)
                            <div class="py-3 flex items-center justify-between text-xs gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center font-black text-xs shrink-0">
                                        {{ $detail->qty }}x
                                    </div>
                                    <div>
                                        <h4 class="text-slate-900 font-bold text-xs">{{ $detail->product->name }}</h4>
                                        @if($detail->custom_length && $detail->custom_width)
                                            <p class="text-[10px] text-slate-500 font-medium mt-0.5">Ukuran Kustom: {{ $detail->custom_length }}m x {{ $detail->custom_width }}m</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0 font-bold text-slate-800">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right Column: Verification Form & Quick Actions -->
            <div class="space-y-6">
                
                <!-- Decision Panel -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                    <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">
                        Keputusan Verifikasi Admin
                    </h2>
                    
                    @if($claim->status === 'pending')
                        <form action="{{ route('admin.claims.status', $claim->id) }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="admin_notes" class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Catatan Hasil Pemeriksaan <span class="text-rose-500">*</span></label>
                                <textarea id="admin_notes" name="admin_notes" required placeholder="Tuliskan alasan hasil pemeriksaan bukti video, misal: 'Kerusakan terverifikasi pada bagian hasil cetak warna pudar'..."
                                          class="w-full rounded-2xl border-slate-200 text-xs py-2.5 px-3 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 h-32 leading-relaxed bg-slate-50/50 font-medium"></textarea>
                            </div>

                            <div class="space-y-2.5 pt-2">
                                <button type="submit" name="status" value="approved" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl shadow-sm text-xs uppercase tracking-wider cursor-pointer transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Setujui Refund Dana
                                </button>

                                <button type="submit" name="status" value="rejected" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl shadow-sm text-xs uppercase tracking-wider cursor-pointer transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Tolak Pengajuan Garansi
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Processed Status Box -->
                        <div class="p-4 border rounded-2xl space-y-3
                            {{ $claim->status === 'approved' ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : 'bg-rose-50/80 border-rose-200 text-rose-900' }}
                        ">
                            <div class="font-black text-xs uppercase tracking-wider flex items-center gap-1.5">
                                {{ $claim->status === 'approved' ? '✅ Garansi Telah Disetujui' : '❌ Garansi Telah Ditolak' }}
                            </div>
                            
                            <div class="text-xs space-y-1">
                                <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Catatan Verifikator</span>
                                <p class="leading-relaxed text-slate-800 font-medium italic">"{{ $claim->admin_notes ?: 'Tidak ada catatan khusus.' }}"</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Quick Contact Box -->
                <div class="bg-slate-900 text-white rounded-3xl p-6 shadow-sm space-y-3">
                    <h3 class="font-black text-xs uppercase tracking-wider text-orange-400 flex items-center gap-2">
                        <span>💬</span> Hubungi Pelanggan
                    </h3>
                    <p class="text-[11px] text-slate-300 leading-relaxed">Gunakan fitur pesan CRM untuk berdiskusi langsung mengenai hasil verifikasi garansi atau konfirmasi transfer refund.</p>
                    <div class="pt-2">
                        <a href="{{ route('admin.show', $claim->order->user_id) }}" class="w-full inline-flex justify-center items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-2xl py-2.5 text-xs uppercase tracking-wider transition-all shadow-sm">
                            Kirim Pesan Chat Direct
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>

