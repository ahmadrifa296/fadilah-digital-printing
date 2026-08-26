<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Chat Bantuan Customer Service</h1>
    </x-slot>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .chat-container {
            height: calc(100vh - 12rem);
            min-height: 500px;
        }
        .chat-sidebar {
            width: 280px;
        }
        .chat-crm-sidebar {
            width: 280px;
        }
        .bubble-me {
            background-color: #ffe8d6;
            color: #7f2d00;
            border-bottom-right-radius: 4px;
        }
        .bubble-other {
            background-color: #f1f5f9;
            color: #1e293b;
            border-bottom-left-radius: 4px;
        }
    </style>
    @endpush

    <div class="chat-container flex bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm"
         x-data="adminChatWorkspace()"
         x-init="initAdminWorkspace()">
        
        <!-- COLUMN 1: Daftar Customer Chat (Sidebar Kiri) -->
        <div class="chat-sidebar flex flex-col border-r border-slate-200 bg-slate-50 flex-shrink-0">
            <div class="p-3 border-b border-slate-200 bg-white">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Daftar Customer</span>
            </div>
            
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 no-scrollbar">
                <template x-for="r in rooms" :key="r.id">
                    <div @click="selectRoom(r)"
                         :class="activeRoomId === r.id ? 'bg-orange-50/70 border-l-4 border-orange-500' : 'hover:bg-slate-100 cursor-pointer'"
                         class="p-3.5 flex gap-3 transition-all relative">
                        
                        <div class="w-9 h-9 rounded-full overflow-hidden border border-slate-200 bg-orange-50 flex items-center justify-center flex-shrink-0">
                            <img :src="r.customer_avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(r.customer_name) + '&color=ea580c&background=ffedd5&rounded=true'" class="w-full h-full object-cover">
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-2xs font-bold text-slate-800 truncate" x-text="r.customer_name"></h4>
                                <span class="text-[9px] text-slate-400" x-text="r.last_message ? r.last_message.time : ''"></span>
                            </div>
                            <p class="text-[10px] text-slate-500 truncate mt-0.5" x-text="r.last_message ? r.last_message.text : 'Belum ada pesan'"></p>
                            
                            <!-- Unread Admin Badge -->
                            <div x-show="r.unread_admin > 0" class="flex justify-end mt-1">
                                <span class="bg-orange-500 text-white rounded-full px-1.5 py-0.5 text-[9px] font-bold" x-text="r.unread_admin"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="rooms.length === 0" class="p-8 text-center text-xs text-slate-400">
                    Belum ada chat.
                </div>
            </div>
        </div>

        <!-- COLUMN 2: Workspace Chat Tengah -->
        <div class="flex-1 flex flex-col justify-between overflow-hidden bg-white">
            
            <template x-if="activeRoomId">
                <div class="flex-1 flex flex-col justify-between overflow-hidden">
                    
                    <!-- Chat Header -->
                    <div class="p-3 border-b border-slate-200 flex items-center justify-between bg-slate-50 flex-shrink-0">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800" x-text="activeCustomerName"></h3>
                            <p class="text-[10px] text-slate-500">Percakapan CS Direct</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Mode Indicator & Toggle -->
                            <button type="button" @click="toggleAiMode(!isAiPaused)"
                                    :class="isAiPaused ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-slate-200 text-slate-700 border-slate-300'"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5 transition-all shadow-3xs cursor-pointer"
                                    :title="isAiPaused ? 'Klik untuk mengaktifkan AI Auto-Reply' : 'Klik untuk mengambil alih ke Chat CS Manual'">
                                <span class="w-2 h-2 rounded-full" :class="isAiPaused ? 'bg-white animate-pulse' : 'bg-orange-500'"></span>
                                <span x-text="isAiPaused ? '🟢 CS Manual Admin (Aktif)' : '🤖 AI Bot Auto-Reply'"></span>
                            </button>
                            <span class="text-[10px] text-slate-400">#<span x-text="activeRoomId"></span></span>
                        </div>
                    </div>

                    <!-- Chat Bubble Area -->
                    <div id="admin-chat-messages-area" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/50">
                        <template x-for="(m, idx) in messages" :key="m.id">
                            <div class="flex flex-col">
                                <!-- Group Tanggal Otomatis -->
                                <div x-show="idx === 0 || messages[idx-1].date !== m.date" class="text-center py-1.5">
                                    <span class="bg-slate-200/80 text-slate-600 px-3 py-1 rounded-full text-[9px] font-bold" x-text="m.date"></span>
                                </div>

                                <div :class="m.is_mine ? 'justify-end' : 'justify-start'" class="flex items-start gap-2.5 mt-3">
                                    
                                    <!-- Customer Avatar -->
                                    <div x-show="!m.is_mine" class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center flex-shrink-0">
                                        <img :src="m.sender_avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(m.sender_name) + '&color=3b82f6&background=dbeafe&rounded=true'" class="w-full h-full object-cover">
                                    </div>

                                    <div class="max-w-[75%]">
                                        <template x-if="!m.is_mine">
                                            <span class="block text-[10px] font-bold text-slate-600 mb-1 ml-1 text-left" x-text="m.sender_name"></span>
                                        </template>
                                        <div :class="m.is_mine ? 'bubble-me rounded-2xl px-4 py-2.5 text-xs shadow-2xs' : 'bubble-other rounded-2xl px-4 py-2.5 text-xs shadow-2xs'" class="text-left">
                                            
                                            <!-- Attachment Rendering -->
                                            <template x-if="m.attachment">
                                                <div class="mb-2 p-2 bg-white/60 border border-slate-200 rounded-lg flex gap-2 items-center">
                                                    <template x-if="m.attachment_type === 'image'">
                                                        <a :href="m.attachment.file_url" target="_blank" class="block w-16 h-16 rounded overflow-hidden flex-shrink-0">
                                                            <img :src="m.attachment.file_url" class="w-full h-full object-cover">
                                                        </a>
                                                    </template>
                                                    <template x-if="m.attachment_type !== 'image'">
                                                        <div class="w-7 h-7 bg-orange-100 text-orange-600 rounded flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        </div>
                                                    </template>
                                                    <div class="min-w-0 text-left">
                                                        <p class="text-3xs font-semibold text-slate-800 truncate" x-text="m.attachment.file_name"></p>
                                                        <a :href="m.attachment.file_url" target="_blank" class="text-[9px] text-orange-600 font-bold hover:underline block mt-0.5">Unduh Berkas</a>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Text Message: use x-html for HTML messages, x-text for plain -->
                                            <template x-if="m.message && m.message.startsWith('<')">
                                                <div class="leading-relaxed text-xs" x-html="m.message"></div>
                                            </template>
                                            <template x-if="!m.message || !m.message.startsWith('<')">
                                                <p class="whitespace-pre-line leading-relaxed" x-text="m.message"></p>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1.5 mt-1 text-[9px] text-slate-400 justify-end">
                                            <span x-text="m.time"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input Box & Quick Replies Area -->
                    <div class="p-3 border-t border-slate-200 bg-white space-y-2.5 flex-shrink-0">
                        <!-- Quick Replies -->
                        <div class="flex gap-1.5 overflow-x-auto pb-1 no-scrollbar">
                            <template x-for="qr in quickReplies" :key="qr.id">
                                <button @click="useQuickReply(qr.message)"
                                        class="flex-shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-[10px] font-bold border border-slate-200/60 transition-colors">
                                    <span x-text="qr.title"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Attachment Preview -->
                        <div x-show="selectedFile" class="p-2 bg-orange-50/50 border border-orange-200/50 rounded-xl flex items-center justify-between text-3xs" x-cloak>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="font-semibold text-slate-700 truncate max-w-[200px]" x-text="selectedFile?.name"></span>
                            </div>
                            <button @click="clearFile()" class="text-red-500 hover:text-red-700 font-bold">Batal</button>
                        </div>

                        <!-- Chat input form -->
                        <form @submit.prevent="sendReply()" class="flex items-center gap-2">
                            <!-- Attachment -->
                            <div>
                                <input type="file" id="admin-attachment-input" @change="handleFileSelect($event)" class="hidden">
                                <button type="button" @click="document.getElementById('admin-attachment-input').click()"
                                        class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </button>
                            </div>

                            <!-- Textbox -->
                            <textarea x-model="typedMessage"
                                      @keydown="handleKeyDown($event)"
                                      placeholder="Tulis balasan Anda..."
                                      rows="1"
                                      class="flex-1 text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 resize-none max-h-24 no-scrollbar"></textarea>

                            <!-- Send -->
                            <button type="submit"
                                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition-colors flex-shrink-0">
                                Balas
                            </button>
                        </form>
                    </div>

                </div>
            </template>

            <template x-if="!activeRoomId">
                <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8">
                    <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-xs">Pilih percakapan customer di sebelah kiri untuk melayani.</p>
                </div>
            </template>

        </div>

        <!-- COLUMN 3: Panel Integrasi CRM Pelanggan (Sidebar Kanan) -->
        <div class="chat-crm-sidebar flex flex-col border-l border-slate-200 bg-slate-50 flex-shrink-0 overflow-y-auto">
            <div class="p-3 border-b border-slate-200 bg-white">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Informasi Customer</span>
            </div>

            <template x-if="activeRoomId && crmData">
                <div class="p-4 space-y-5 text-xs text-slate-700">
                    
                    <!-- Profil dasar -->
                    <div class="space-y-3 pb-4 border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full overflow-hidden border border-slate-200 bg-orange-50 flex items-center justify-center flex-shrink-0 shadow-3xs">
                                <img :src="crmData.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(crmData.name) + '&color=ea580c&background=ffedd5&rounded=true'" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 text-sm truncate" x-text="crmData.name"></h4>
                                <span class="text-[9px] text-slate-400 block" x-text="'Member sejak: ' + crmData.member_since"></span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Email</span>
                            <span class="text-slate-600 block break-all font-medium" x-text="crmData.email"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Nomor HP</span>
                            <span class="text-slate-600 font-medium" x-text="crmData.phone"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Alamat Utama</span>
                            <span class="text-slate-600 block leading-tight text-[11px]" x-text="crmData.address"></span>
                        </div>
                    </div>

                    <!-- Ringkasan Transaksi -->
                    <div class="space-y-2 pb-4 border-b border-slate-200">
                        <h4 class="font-bold text-slate-800 text-2xs uppercase tracking-wide">Ringkasan Belanja</h4>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Total Transaksi:</span>
                            <span class="font-bold text-slate-800" x-text="crmData.total_orders + ' Pesanan'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Total Belanja:</span>
                            <span class="font-bold text-emerald-600" x-text="crmData.total_spent"></span>
                        </div>
                    </div>

                    <!-- Riwayat Pembelian Terakhir -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-800 text-2xs uppercase tracking-wide">Pembelian Terakhir</h4>
                        
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Invoice Terakhir</span>
                            <template x-if="crmData.latest_invoice_id">
                                <a :href="'/pesanan/' + crmData.latest_invoice_id" target="_blank"
                                   class="text-orange-600 hover:text-orange-700 font-mono font-bold hover:underline"
                                   x-text="crmData.latest_invoice"></a>
                            </template>
                            <template x-if="!crmData.latest_invoice_id">
                                <span class="text-slate-400 font-mono" x-text="crmData.latest_invoice"></span>
                            </template>
                        </div>

                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Total Tagihan</span>
                            <span class="font-bold text-slate-800" x-text="crmData.latest_total"></span>
                        </div>

                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Status Pembayaran</span>
                            <span class="font-bold" x-text="crmData.latest_status"></span>
                        </div>

                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Tracking Produksi</span>
                            <span class="badge-neutral" x-text="crmData.latest_tracking"></span>
                        </div>
                    </div>

                </div>
            </template>

            <template x-if="!activeRoomId">
                <div class="p-8 text-center text-xs text-slate-400 italic">
                    Pilih customer terlebih dahulu.
                </div>
            </template>
        </div>

    </div>

    @push('scripts')
    <script>
        function adminChatWorkspace() {
            return {
                rooms: [],
                messages: [],
                quickReplies: [],
                activeRoomId: null,
                activeCustomerId: null,
                activeCustomerName: '',
                crmData: null,
                isAiPaused: false,
                typedMessage: '',
                selectedFile: null,
                roomsIntervalId: null,
                messagesIntervalId: null,

                initAdminWorkspace() {
                    this.pollRooms();
                    this.roomsIntervalId = setInterval(() => this.pollRooms(), 4000);
                    this.messagesIntervalId = setInterval(() => this.pollMessages(), 3000);

                    // Load quick replies dari Laravel collection
                    this.quickReplies = @json($quickReplies);
                },

                clearIntervals() {
                    if (this.roomsIntervalId) clearInterval(this.roomsIntervalId);
                    if (this.messagesIntervalId) clearInterval(this.messagesIntervalId);
                },

                pollRooms() {
                    fetch('{{ route('chat.rooms.poll') }}?status=active', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(res => {
                            if (res.status === 401) {
                                this.clearIntervals();
                                return null;
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return;
                            this.rooms = data.rooms || [];
                            // Auto select first customer room if none active yet
                            if (!this.activeRoomId && this.rooms.length > 0) {
                                this.selectRoom(this.rooms[0]);
                            }
                        })
                        .catch(err => console.error("Poll rooms error:", err));
                },

                selectRoom(room) {
                    if (!room) return;
                    this.activeRoomId = room.id;
                    this.activeCustomerId = room.customer_id;
                    this.activeCustomerName = room.customer_name || 'Customer';
                    this.messages = [];
                    this.crmData = null;

                    fetch(`/admin/chat/room/${room.id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(res => {
                            if (!res.ok) {
                                console.error("adminShow HTTP error:", res.status, res.statusText);
                                throw new Error("Gagal memuat room chat: " + res.status);
                            }
                            return res.json();
                        })
                        .then(data => {
                            console.log("adminShow response:", data);
                            if (!data || !data.room) {
                                console.error("No data.room in response!");
                                return;
                            }
                            this.activeRoomId = data.room.id;
                            this.activeCustomerName = data.room.customer_name || 'Customer';
                            this.isAiPaused = data.room.is_ai_paused || false;
                            this.messages = data.messages || [];
                            this.crmData = data.crm || null;
                            console.log("messages loaded:", this.messages.length, "crm:", this.crmData);
                            this.scrollToBottom();
                        })
                        .catch(err => console.error("Load room error:", err));
                },

                toggleAiMode(disableAi) {
                    if (!this.activeRoomId) return;
                    const enableAi = !disableAi;
                    fetch(`/admin/chat/${this.activeRoomId}/toggle-ai`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enable: enableAi })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.isAiPaused = disableAi;
                        }
                    })
                    .catch(err => console.error("Toggle AI error:", err));
                },

                pollMessages() {
                    if (!this.activeRoomId) return;

                    fetch(`/admin/chat/${this.activeRoomId}/poll`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(res => {
                            if (res.status === 401) {
                                this.clearIntervals();
                                return null;
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data) return;
                            const oldLength = this.messages.length;
                            this.messages = data.messages || [];
                            if (data.crm) {
                                this.crmData = data.crm;
                            }

                            if (this.messages.length > oldLength) {
                                this.scrollToBottom();
                            }
                        })
                        .catch(err => console.error("Poll messages error:", err));
                },

                sendReply() {
                    if (this.typedMessage.trim() === '' && !this.selectedFile) return;

                    const formData = new FormData();
                    formData.append('room_id', this.activeRoomId);
                    formData.append('message', this.typedMessage);
                    if (this.selectedFile) {
                        formData.append('attachment', this.selectedFile);
                    }

                    fetch('{{ route('admin.reply') }}', {
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
                            this.clearIntervals();
                            return null;
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            this.typedMessage = '';
                            this.isAiPaused = true;
                            this.clearFile();
                            this.pollMessages();
                            this.scrollToBottom();
                        } else {
                            alert(data.error || "Gagal membalas chat.");
                        }
                    })
                    .catch(err => console.error("Reply error:", err));
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
                    const input = document.getElementById('admin-attachment-input');
                    if (input) input.value = '';
                },

                useQuickReply(text) {
                    this.typedMessage = text;
                },

                handleKeyDown(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendReply();
                    }
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const area = document.getElementById('admin-chat-messages-area');
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
