<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title text-slate-800">💬 Chat Bantuan CS</h1>
            <p class="page-subtitle text-slate-500">Hubungi tim pelayanan pelanggan kami secara langsung untuk bantuan produk dan pesanan</p>
        </div>
    </x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .chat-container {
            height: calc(100vh - 14rem);
            min-height: 500px;
        }
        .bubble-me {
            background-color: #fff7ed;
            color: #ea580c;
            border-bottom-right-radius: 4px;
            border: 1px solid #ffedd5;
        }
        .bubble-other {
            background-color: #f8fafc;
            color: #334155;
            border-bottom-left-radius: 4px;
            border: 1px solid #f1f5f9;
        }
    </style>
    @endpush

    <div class="max-w-4xl mx-auto animate-fade-in" x-data="customerChat()" x-init="initChat()">
        <div class="chat-container flex flex-col bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            
            <!-- HEADER: Shopee Chat Style (Single Room) -->
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-extrabold text-sm uppercase shadow-sm">
                        F
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800">Fadilah Digital Printing</h3>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                            <span class="font-semibold text-emerald-600 font-sans">Online &middot; Customer Service</span>
                        </p>
                    </div>
                </div>
                <div class="text-[9px] text-slate-400 font-extrabold uppercase bg-white border border-slate-200 px-2.5 py-0.5 rounded-full">
                    Room: #<span x-text="room?.id"></span>
                </div>
            </div>

            <!-- CHAT AREA: Message bubble history -->
            <div id="chat-messages-area" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/30">
                
                <!-- Rujukan Produk / Invoice dari Detail -->
                <div x-show="linkedProduct" class="bg-orange-50/80 border border-orange-200/60 rounded-2xl p-3.5 flex gap-3 items-center justify-between text-xs max-w-md mx-auto shadow-sm animate-fade-in" x-cloak>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-200">
                            <img :src="linkedProduct?.image_url" class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="font-bold text-slate-800 truncate block" x-text="linkedProduct?.name"></span>
                            <span class="text-orange-500 font-bold block text-3xs" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(linkedProduct?.price)"></span>
                        </div>
                    </div>
                    <button @click="sendLinkedProduct()" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[9px] px-3 py-1.5 rounded-lg flex-shrink-0 cursor-pointer shadow-3xs uppercase tracking-wider">
                        Kirim Rujukan
                    </button>
                </div>

                <div x-show="linkedOrder" class="bg-orange-50/80 border border-orange-200/60 rounded-2xl p-3.5 flex gap-3 items-center justify-between text-xs max-w-md mx-auto shadow-sm animate-fade-in" x-cloak>
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-slate-800 block text-3xs" x-text="'Invoice: ' + linkedOrder?.invoice"></span>
                        <span class="text-slate-500 text-[10px] block mt-0.5" x-text="'Status: ' + linkedOrder?.status"></span>
                    </div>
                    <button @click="sendLinkedOrder()" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[9px] px-3 py-1.5 rounded-lg flex-shrink-0 cursor-pointer shadow-3xs uppercase tracking-wider">
                        Kirim Rujukan
                    </button>
                </div>

                <!-- Messages -->
                <template x-for="(m, idx) in messages" :key="m.id">
                    <div class="flex flex-col">
                        <!-- Group Tanggal Otomatis -->
                        <div x-show="idx === 0 || messages[idx-1].date !== m.date" class="text-center py-3 animate-fade-in">
                            <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider border border-slate-200/40" x-text="m.date"></span>
                        </div>

                        <!-- Message bubble wrapper -->
                        <div :class="m.is_mine ? 'justify-end' : 'justify-start'" class="flex items-start gap-2.5 mt-3 group animate-fade-in">
                            
                            <!-- Admin avatar (other) -->
                            <div x-show="!m.is_mine" class="w-8 h-8 rounded-lg bg-orange-500 text-white flex-shrink-0 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                F
                            </div>

                            <div class="max-w-[70%] space-y-1">
                                <div :class="m.is_mine ? 'bubble-me' : 'bubble-other'" class="rounded-2xl px-4 py-2.5 text-xs shadow-3xs text-left">
                                    
                                    <!-- Attachment Rendering -->
                                    <template x-if="m.attachment">
                                        <div class="mb-2 p-2 bg-white/70 border border-slate-200/60 rounded-xl flex gap-2.5 items-center text-slate-800">
                                            <template x-if="m.attachment_type === 'image'">
                                                <a :href="m.attachment.file_url" target="_blank" class="block w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 border border-slate-200 hover:scale-105 transition-transform">
                                                    <img :src="m.attachment.file_url" class="w-full h-full object-cover">
                                                </a>
                                            </template>
                                            <template x-if="m.attachment_type !== 'image'">
                                                <div class="w-8 h-8 bg-orange-55/70 text-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 border border-orange-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </div>
                                            </template>
                                            <div class="min-w-0 text-left flex-1">
                                                <p class="text-[10px] font-bold truncate" x-text="m.attachment.file_name"></p>
                                                <a :href="m.attachment.file_url" target="_blank" class="text-[9px] text-orange-500 font-extrabold hover:underline block mt-0.5 uppercase tracking-wider">Unduh Berkas</a>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Message Text -->
                                    <p class="whitespace-pre-line leading-relaxed font-medium" x-html="m.message"></p>
                                </div>

                                <!-- Metadata info -->
                                <div class="flex items-center gap-1.5 text-[8px] text-slate-400 font-bold uppercase tracking-wider" :class="m.is_mine ? 'justify-end' : 'justify-start'">
                                    <span x-text="m.time"></span>
                                    <template x-if="m.is_mine">
                                        <div class="flex items-center gap-0.5">
                                            <span x-show="m.is_read" class="text-orange-500 font-bold">✔ dibaca</span>
                                            <span x-show="!m.is_read" class="text-slate-300">✔ terkirim</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- INPUT BOX: Shopee Chat Input Bar -->
            <div class="p-4 border-t border-slate-200/80 bg-white space-y-3 flex-shrink-0">
                <!-- Attachment preview before send -->
                <div x-show="selectedFile" class="p-2.5 bg-orange-50/50 border border-orange-200/50 rounded-xl flex items-center justify-between text-xs" x-cloak>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span class="font-bold text-slate-700 truncate max-w-[250px]" x-text="selectedFile?.name"></span>
                    </div>
                    <button @click="clearFile()" class="text-red-500 hover:text-red-700 font-bold text-3xs uppercase tracking-wider">Batal</button>
                </div>

                <!-- Input area -->
                <form @submit.prevent="sendMessage()" class="flex items-center gap-3">
                    <!-- File input button -->
                    <div>
                        <input type="file" id="chat-attachment-input" @change="handleFileSelect($event)" class="hidden">
                        <button type="button" @click="document.getElementById('chat-attachment-input').click()"
                                class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-xl transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </button>
                    </div>

                    <!-- Emoji inline button -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-xl transition-colors cursor-pointer text-sm">
                            😊
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute bottom-full left-0 mb-2 p-2 bg-white border border-slate-200 rounded-xl shadow-lg flex gap-1.5 z-50 animate-fade-in">
                            <template x-for="emo in ['👋', '😊', '👍', '🙏', '🔥', '❤']">
                                <button type="button" @click="insertEmoji(emo); open = false;" class="hover:scale-125 transition-transform text-base cursor-pointer" x-text="emo"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Textarea input -->
                    <textarea x-model="typedMessage"
                              @keydown="handleKeyDown($event)"
                              placeholder="Tulis pesan untuk CS Fadilah Printing..."
                              rows="1"
                              class="flex-1 text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 resize-none max-h-24 no-scrollbar"></textarea>

                    <!-- Send button -->
                    <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-extrabold px-5 py-2.5 rounded-xl text-xs transition-colors flex-shrink-0 shadow-md hover:shadow-orange-500/10 cursor-pointer uppercase tracking-wider">
                        Kirim
                    </button>
                </form>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function customerChat() {
            return {
                room: null,
                messages: [],
                typedMessage: '',
                selectedFile: null,
                pollIntervalId: null,
                
                linkedProduct: null,
                linkedOrder: null,

                initChat() {
                    this.pollMessages(true);
                    this.pollIntervalId = setInterval(() => this.pollMessages(), 3000);

                    @if(isset($linkedProduct))
                        this.linkedProduct = {
                            id: {{ $linkedProduct->id }},
                            name: "{{ $linkedProduct->product_name }}",
                            price: {{ $linkedProduct->price }},
                            image_url: "{{ Storage::url($linkedProduct->image) }}"
                        };
                    @endif

                    @if(isset($linkedOrder))
                        this.linkedOrder = {
                            id: {{ $linkedOrder->id }},
                            invoice: "{{ $linkedOrder->invoice_number }}",
                            status: "{{ $linkedOrder->order_status }}"
                        };
                    @endif
                },

                pollMessages(forceScroll = false) {
                    fetch('{{ route('chat.poll') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(res => {
                            if (res.status === 401) {
                                if (this.pollIntervalId) clearInterval(this.pollIntervalId);
                                return null;
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return;
                            this.room = data.room;
                            const oldLength = this.messages.length;
                            this.messages = data.messages || [];

                            if (forceScroll || this.messages.length > oldLength) {
                                this.scrollToBottom();
                            }
                        })
                        .catch(err => console.error("Poll error:", err));
                },

                sendMessage() {
                    if (this.typedMessage.trim() === '' && !this.selectedFile) return;

                    const formData = new FormData();
                    formData.append('message', this.typedMessage);
                    if (this.selectedFile) {
                        formData.append('attachment', this.selectedFile);
                    }

                    fetch('{{ route('chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => {
                        if (res.status === 401) {
                            if (this.pollIntervalId) clearInterval(this.pollIntervalId);
                            return null;
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            this.typedMessage = '';
                            this.clearFile();
                            this.pollMessages(true);
                        } else {
                            alert(data.error || "Gagal mengirim pesan.");
                        }
                    })
                    .catch(err => console.error("Send error:", err));
                },

                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 10 * 1024 * 1024) {
                            alert("Berkas terlalu besar! Maksimal ukuran adalah 10MB.");
                            return;
                        }
                        this.selectedFile = file;
                    }
                },

                clearFile() {
                    this.selectedFile = null;
                    const input = document.getElementById('chat-attachment-input');
                    if (input) input.value = '';
                },

                insertEmoji(emo) {
                    this.typedMessage += emo;
                },

                handleKeyDown(e) {
                    // Enter = Kirim, Shift+Enter = Baris Baru
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                },

                sendLinkedProduct() {
                    if (!this.linkedProduct) return;
                    this.typedMessage = `[Rujukan Produk]\nNama: ${this.linkedProduct.name}\nHarga: Rp ${new Intl.NumberFormat('id-ID').format(this.linkedProduct.price)}`;
                    this.sendMessage();
                    this.linkedProduct = null;
                },

                sendLinkedOrder() {
                    if (!this.linkedOrder) return;
                    this.typedMessage = `[Rujukan Invoice]\nNomor Invoice: ${this.linkedOrder.invoice}\nStatus: ${this.linkedOrder.status}`;
                    this.sendMessage();
                    this.linkedOrder = null;
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const area = document.getElementById('chat-messages-area');
                        if (area) {
                            area.scrollTop = area.scrollHeight;
                        }
                    }, 50);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
