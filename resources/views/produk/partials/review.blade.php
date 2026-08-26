<div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm"
     x-data="productReviews()">
    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Penilaian & Ulasan Produk</h3>
    
    <div class="card-body p-0 pt-6 space-y-6">
        <!-- Rating Header Stats (Shopee Style Grid) -->
        <div class="bg-slate-50/70 border border-slate-200/40 p-5 rounded-2xl flex flex-col lg:flex-row items-center gap-6">
            <div class="text-center lg:border-r border-slate-200/70 lg:pr-8 flex-shrink-0">
                <p class="text-3xl font-black text-orange-500">{{ number_format($visibleReviews->avg('rating') ?: 5.0, 1) }}</p>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Dari 5 Bintang</p>
                <div class="flex text-orange-400 text-xs justify-center mt-1.5">
                    @php $ratingVal = (int) round($visibleReviews->avg('rating') ?: 5); @endphp
                    @for($i=1; $i<=5; $i++)
                        <span>{{ $i <= $ratingVal ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <p class="text-[9px] text-slate-400 mt-2 font-semibold">({{ $visibleReviews->count() }} Ulasan Real)</p>
            </div>

            <!-- Stars Filters Badge (Shopee Style Buttons) -->
            <div class="flex flex-wrap gap-2 text-[10px] font-bold text-slate-600">
                <button type="button" @click="filterRating = 'all'"
                        :class="filterRating === 'all' ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    Semua (<span x-text="countAll"></span>)
                </button>
                <button type="button" @click="filterRating = 5"
                        :class="filterRating === 5 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    5 Bintang (<span x-text="count5"></span>)
                </button>
                <button type="button" @click="filterRating = 4"
                        :class="filterRating === 4 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    4 Bintang (<span x-text="count4"></span>)
                </button>
                <button type="button" @click="filterRating = 3"
                        :class="filterRating === 3 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    3 Bintang (<span x-text="count3"></span>)
                </button>
                <button type="button" @click="filterRating = 2"
                        :class="filterRating === 2 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    2 Bintang (<span x-text="count2"></span>)
                </button>
                <button type="button" @click="filterRating = 1"
                        :class="filterRating === 1 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    1 Bintang (<span x-text="count1"></span>)
                </button>
                <button type="button" @click="filterRating = 'with_photo'"
                        :class="filterRating === 'with_photo' ? 'bg-orange-500 text-white shadow-3xs' : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer focus:outline-none">
                    Dengan Foto (<span x-text="countWithPhoto"></span>)
                </button>
            </div>
        </div>

        <!-- Review list template -->
        @if($visibleReviews->isEmpty())
            <div class="text-center py-10 space-y-2.5">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.52 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.178 0l-3.97 2.883c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.783-.57-.38-1.81.588-1.81h4.906a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <h4 class="font-bold text-slate-800 text-xs">Belum Ada Ulasan untuk Produk Ini</h4>
                <p class="text-[10px] text-slate-400 font-normal">Beli produk ini dan jadilah yang pertama memberikan review!</p>
            </div>
        @else
            <!-- Loop ulasan menggunakan Alpine.js -->
            <div class="divide-y divide-slate-100" x-show="filteredReviews.length > 0">
                <template x-for="rev in filteredReviews" :key="rev.id">
                    <div class="py-5 space-y-3">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <!-- Profile Initials Avatar -->
                                <div class="h-8 w-8 rounded-full overflow-hidden border border-slate-200 bg-orange-50 flex items-center justify-center shadow-3xs flex-shrink-0">
                                    <img :src="rev.user.avatar_url ? (rev.user.avatar_url.startsWith('http') ? rev.user.avatar_url : (rev.user.avatar_url.startsWith('/') ? rev.user.avatar_url : '/storage/' + rev.user.avatar_url)) : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' + encodeURIComponent(rev.user.name)" :alt="rev.user.name" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-xs" x-text="rev.user.name"></h4>
                                    <div class="flex text-orange-400 text-2xs mt-0.5 items-center gap-2">
                                        <div class="flex">
                                            <template x-for="star in 5">
                                                <span x-text="star <= rev.rating ? '★' : '☆'"></span>
                                            </template>
                                        </div>
                                        <span class="text-[9px] text-slate-400 font-normal" x-text="rev.created_at_human"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment -->
                        <p class="text-xs text-slate-600 leading-relaxed font-normal pl-11" x-text="rev.comment"></p>

                        <!-- Review Image Attachment -->
                        <template x-if="rev.photo">
                            <div class="pl-11 mt-2">
                                <a :href="rev.photo" target="_blank" class="inline-block relative rounded-xl overflow-hidden border border-slate-200/80 aspect-square w-20 group">
                                    <img :src="rev.photo" class="object-cover w-full h-full transition-transform duration-200 group-hover:scale-105">
                                </a>
                            </div>
                        </template>

                        <!-- Admin Reply -->
                        <template x-if="rev.reply">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/40 mt-3 text-xs ml-11 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800">Balasan Admin</span>
                                    <span class="text-[9px] bg-slate-900 text-white font-bold px-1.5 py-0.2 rounded-md uppercase">Toko</span>
                                </div>
                                <p class="text-slate-600 leading-relaxed font-normal" x-text="rev.reply"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Empty Filter State -->
            <div class="text-center py-12 space-y-2.5" x-show="filteredReviews.length === 0" x-cloak>
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h4 class="font-bold text-slate-800 text-xs">Tidak ada ulasan dengan rating ini.</h4>
                <p class="text-[10px] text-slate-400 font-normal">Silakan pilih filter ulasan lainnya.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function productReviews() {
        return {
            filterRating: 'all',
            reviews: @json($reviewsPayload),
            get countAll() { return this.reviews.length; },
            get count5() { return this.reviews.filter(r => r.rating === 5).length; },
            get count4() { return this.reviews.filter(r => r.rating === 4).length; },
            get count3() { return this.reviews.filter(r => r.rating === 3).length; },
            get count2() { return this.reviews.filter(r => r.rating === 2).length; },
            get count1() { return this.reviews.filter(r => r.rating === 1).length; },
            get countWithPhoto() { return this.reviews.filter(r => r.photo).length; },
            get filteredReviews() {
                if (this.filterRating === 'all') return this.reviews;
                if (this.filterRating === 'with_photo') return this.reviews.filter(r => r.photo);
                return this.reviews.filter(r => r.rating === parseInt(this.filterRating));
            }
        };
    }
</script>
@endpush

