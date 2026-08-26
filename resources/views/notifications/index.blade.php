<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="page-title text-slate-800">🔔 Notifikasi Saya</h1>
                <p class="page-subtitle text-slate-500">Pantau semua pembaruan pesanan, promo, akun, dan pesan penting Anda.</p>
            </div>
            @if($notifications->total() > 0)
                <form action="{{ route('notifications.read_all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-md bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow-sm cursor-pointer">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2 animate-fade-in text-xs font-semibold">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Filter Tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
            @php
                $tabs = [
                    'semua' => 'Semua',
                    'pesanan' => '📦 Pesanan',
                    'pembayaran' => '💳 Pembayaran',
                    'produk' => '🏷️ Produk',
                    'promo' => '🔥 Promo',
                    'chat' => '💬 Chat',
                    'akun' => '👤 Akun',
                    'belum_dibaca' => '🔵 Belum Dibaca',
                    'sudah_dibaca' => '✓ Sudah Dibaca',
                ];
            @endphp
            @foreach($tabs as $key => $label)
                <a href="{{ route('notifications.index', ['filter' => $key]) }}"
                   class="px-4 py-2 text-xs font-bold rounded-full whitespace-nowrap transition border {{ $filter === $key ? 'bg-orange-500 text-white border-orange-500 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Notification List --}}
        @if($notifications->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center max-w-md mx-auto space-y-4">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto border border-slate-100 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-slate-800 text-sm">Tidak Ada Notifikasi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kami tidak menemukan notifikasi di bawah filter ini saat ini.
                    </p>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
                @foreach($notifications as $n)
                    @php
                        $isUnread = is_null($n->read_at);
                        $nTitle = $n->data['title'] ?? 'Pemberitahuan';
                        $nContent = $n->data['content'] ?? '';
                        $nIcon = $n->data['icon'] ?? 'bell';
                        $nColor = $n->data['color'] ?? 'orange';
                        $nUrl = route('notifications.click', $n->id);
                        
                        $bgClass = $isUnread ? 'bg-orange-50/20 border-l-4 border-orange-500' : '';
                        $iconBgMap = [
                            'blue' => 'bg-blue-500 text-white',
                            'orange' => 'bg-orange-500 text-white',
                            'green' => 'bg-green-500 text-white',
                            'red' => 'bg-red-500 text-white',
                            'amber' => 'bg-amber-500 text-white',
                            'yellow' => 'bg-yellow-500 text-white',
                            'purple' => 'bg-purple-500 text-white',
                            'pink' => 'bg-pink-500 text-white',
                            'cyan' => 'bg-cyan-500 text-white',
                            'slate' => 'bg-slate-500 text-white',
                        ];
                        $iconBg = $iconBgMap[$nColor] ?? 'bg-orange-500 text-white';
                    @endphp
                    <a href="{{ $nUrl }}" class="block p-4 sm:p-5 hover:bg-slate-50 transition flex gap-4 {{ $bgClass }}">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-2xl flex items-center justify-center {{ $iconBg }} shadow-sm">
                            @if($nIcon === 'shopping-bag')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            @elseif($nIcon === 'shopping-cart')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @elseif($nIcon === 'credit-card')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @elseif($nIcon === 'truck')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 9h4l3 3v5h-2M1 3h11v12M13 9V5a1 1 0 00-1-1H9"/></svg>
                            @elseif($nIcon === 'chat')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @elseif($nIcon === 'key')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V4a2 2 0 00-2-2h-3a2 2 0 00-2 2v3m2 3H3a2 2 0 00-2 2v3a2 2 0 002 2h3a2 2 0 002-2v-3a2 2 0 00-2-2z"/></svg>
                            @elseif($nIcon === 'tag')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif($nIcon === 'percent')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15l6-6m-5 6h.01M14 9h.01M3 21h18M3 10h18M3 7h18M3 4h18"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-grow min-w-0">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="font-bold text-slate-800 text-sm leading-snug">{{ $nTitle }}</h3>
                                <span class="text-[10px] text-slate-400 font-bold whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $nContent }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
