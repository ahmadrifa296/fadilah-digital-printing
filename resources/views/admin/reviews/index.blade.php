@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Moderasi Ulasan & Rating</h1>
            <p class="mt-2 text-sm text-gray-600">Pantau, balas, atau moderasi (sembunyikan/hapus) ulasan yang dikirim oleh pelanggan.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Statistik Rating Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-center items-center">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Rata-rata Rating</span>
                <span class="text-5xl font-black text-gray-900">{{ $averageRating }}</span>
                <div class="flex items-center text-amber-400 mt-2 text-xl">
                    @for($i=1; $i<=5; $i++)
                        <span>★</span>
                    @endfor
                </div>
                <span class="text-xs text-gray-500 mt-2">Berdasarkan {{ $totalReviews }} total ulasan</span>
            </div>

            <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Statistik Distribusi Rating</h3>
                <div class="space-y-3">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $count = $ratingStats[$star] ?? 0;
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center text-xs">
                            <span class="w-12 font-semibold text-gray-600">{{ $star }} Bintang</span>
                            <div class="flex-1 mx-4 h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="w-8 text-right font-bold text-gray-900">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Daftar Ulasan -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Ulasan Pelanggan</h2>
            </div>

            @if($reviews->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.52 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.178 0l-3.97 2.883c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.783-.57-.38-1.81.588-1.81h4.906a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada ulasan</h3>
                    <p class="mt-1 text-sm text-gray-500">Ulasan dari verified purchase akan muncul secara otomatis setelah pesanan selesai.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($reviews as $rev)
                        <div class="p-6 space-y-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm overflow-hidden flex-shrink-0">
                                        {{ strtoupper(substr($rev->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $rev->user->name }}</h4>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                Verified Purchase
                                            </span>
                                        </div>
                                        <div class="flex items-center text-amber-400 mt-1">
                                            @for($i=1; $i<=5; $i++)
                                                <span class="text-sm">{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                                            @endfor
                                            <span class="text-xs text-gray-400 ml-2 font-medium">Order #{{ $rev->order->invoice_number }} &bull; {{ $rev->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 mt-2 font-medium">Produk: <span class="text-indigo-600">{{ $rev->product?->product_name ?? 'Produk Terhapus' }}</span></p>
                                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $rev->comment }}</p>
                                        
                                        @if($rev->photo)
                                            <div class="mt-3">
                                                <a href="{{ $rev->photo }}" target="_blank" class="inline-block relative rounded-lg overflow-hidden border border-gray-100 aspect-square w-24">
                                                    <img src="{{ $rev->photo }}" class="object-cover w-full h-full">
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 flex-shrink-0 self-end md:self-start">
                                    <!-- Toggle Visibility -->
                                    <form action="{{ route('reviews.toggle_visibility', $rev->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center text-xs font-semibold {{ $rev->is_visible ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }} px-3 py-1.5 rounded-lg transition-colors">
                                            {{ $rev->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('reviews.destroy', $rev->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus ulasan ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Balasan Admin -->
                            @if($rev->reply)
                                <div class="ml-14 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100/30 text-sm">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="font-bold text-gray-900">Balasan Fadilah Printing</span>
                                        <span class="text-[10px] bg-indigo-100 text-indigo-800 font-bold px-1.5 py-0.5 rounded">Admin</span>
                                    </div>
                                    <p class="text-gray-600 leading-relaxed">{{ $rev->reply }}</p>
                                </div>
                            @else
                                <div class="ml-14">
                                    <form action="{{ route('reviews.reply', $rev->id) }}" method="POST" class="max-w-xl space-y-3">
                                        @csrf
                                        <div>
                                            <textarea name="reply" rows="2" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-xs" placeholder="Tulis balasan resmi untuk ulasan pelanggan..." required></textarea>
                                        </div>
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-bold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                            Kirim Balasan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
