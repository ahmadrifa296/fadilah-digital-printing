{{-- Order Filter Tabs + Search — Shopee Slim Style --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
   {{-- Tab Bar --}}
   <div class="flex overflow-x-auto divide-x divide-slate-100 scrollbar-none border-b border-slate-100">
       @foreach([
           ['key' => 'all',        'label' => 'Semua'],
           ['key' => 'unpaid',     'label' => 'Belum Bayar'],
           ['key' => 'processing', 'label' => 'Diproses'],
           ['key' => 'printing',   'label' => 'Dicetak'],
           ['key' => 'packing',    'label' => 'Dikemas'],
           ['key' => 'shipping',   'label' => 'Dikirim'],
           ['key' => 'done',       'label' => 'Selesai'],
           ['key' => 'cancelled',  'label' => 'Dibatalkan'],
       ] as $tab)
       <button @click="activeTab = '{{ $tab['key'] }}'"
               :class="activeTab === '{{ $tab['key'] }}' ? 'border-b-2 border-orange-500 text-orange-500 font-bold bg-orange-50/40' : 'text-slate-500 hover:bg-slate-50 font-medium'"
               class="flex-1 min-w-[80px] py-2.5 px-2 text-center text-[10px] transition-colors shrink-0 cursor-pointer whitespace-nowrap">
           {{ $tab['label'] }}
       </button>
       @endforeach
   </div>

   {{-- Search Bar --}}
   <div class="px-4 py-2.5 flex items-center gap-3">
       <div class="relative flex-1">
           <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
           </svg>
           <input type="text"
                  x-model="searchQuery"
                  placeholder="Cari No. Invoice, produk, atau status..."
                  class="w-full bg-slate-50 border border-slate-200 rounded-lg h-9 pl-9 pr-3 text-[11px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 transition-colors">
       </div>
   </div>
</div>

{{-- Order Cards --}}
<div class="space-y-3 mt-3">
   @php $ordersFound = $myOrders->count() > 0; @endphp

   @if($ordersFound)
       @foreach($myOrders as $order)
           <div class="order-card-item"
                x-show="matchesTab('{{ $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status }}', '{{ $order->tracking_status }}') &&
                             ('{{ strtolower($order->invoice_number) }}'.includes(searchQuery.toLowerCase()) ||
                              '{{ strtolower($order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status) }}'.includes(searchQuery.toLowerCase()) ||
                              '{{ strtolower($order->created_at->format('d M Y')) }}'.includes(searchQuery.toLowerCase()) ||
                              '{{ strtolower($order->orderDetails->pluck('product.product_name')->implode(', ')) }}'.includes(searchQuery.toLowerCase()))"
                x-transition:enter="transition ease-out duration-200">
               @include('dashboard.partials.order-card', ['order' => $order])
           </div>
       @endforeach
   @endif

   {{-- Empty state --}}
   <div x-show="getVisibleCount() === 0" x-cloak>
       @include('dashboard.partials.empty-state')
   </div>
</div>
