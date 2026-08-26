<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title text-slate-800">Dashboard Saya</h1>
            <p class="page-subtitle text-slate-500">Kelola informasi profil, pesanan, dan ulasan belanja Anda secara praktis</p>
        </div>
    </x-slot>

    {{-- Main Workspace Container --}}
    <div class="space-y-6" 
         x-data="{ 
             activeTab: 'all', 
             searchQuery: '',
             reviewModalOpen: false,
             activeOrder: null,
             activeProduct: null,
             rating: 5,
             hoverRating: 0,
             
             matchesTab(status, tracking) {
                 if (this.activeTab === 'all') return true;
                 if (this.activeTab === 'unpaid') return status === 'pending';
                 if (this.activeTab === 'processing') return status === 'paid' || status === 'diproses';
                 if (this.activeTab === 'printing') return status === 'sedang_dicetak';
                 if (this.activeTab === 'packing') return ['siap_dikemas', 'dikemas'].includes(status);
                 if (this.activeTab === 'shipping') return status === 'dikirim';
                 if (this.activeTab === 'done') return status === 'selesai';
                 if (this.activeTab === 'cancelled') return status === 'dibatalkan';
                 return false;
             },
             
             getVisibleCount() {
                 return Array.from(document.querySelectorAll('.order-card-item')).filter(el => el.style.display !== 'none').length;
             }
         }">

        <!-- 1. Header Profile Info -->
        @include('dashboard.partials.header')

        <!-- 2. Orders List, Filter & Search -->
        @include('dashboard.partials.orders')

        <!-- 3. Alpine.js Review Modal -->
        @include('dashboard.partials.review-modal')

    </div>
</x-app-layout>