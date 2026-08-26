<style>
    @media (max-width: 640px) {
        #widget-chat-panel {
            position: fixed !important;
            bottom: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 0 !important;
        }
    }
</style>

<div id="floating-chat-widget" x-data="chatWidget()" x-init="initChatWidget()" style="position:fixed; bottom:24px; right:24px; z-index:99999;">
    
    <!-- Floating Circular Button -->
    <button @click="openChatPanel()" 
            x-show="!isOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-75 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative flex items-center justify-center w-16 h-16 bg-orange-500 hover:bg-orange-600 text-white rounded-full shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 group focus:outline-none cursor-pointer border border-orange-400">
        <svg class="w-7 h-7 text-white group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        
        <!-- Badge for Unread Messages (only if logged in) -->
        @auth
        <span x-show="unreadCount > 0" 
              class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-extrabold text-white shadow-md border-2 border-white animate-bounce"
              x-text="unreadCount"
              x-cloak></span>
        @endauth
    </button>

    <!-- Chat Panel Window -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-8"
         class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 flex flex-col overflow-hidden transition-all duration-300 max-sm:fixed max-sm:inset-0 max-sm:w-full max-sm:h-full max-sm:rounded-none"
         style="position:fixed; bottom:24px; right:24px; z-index:99999; width:380px; height:650px;"
         id="widget-chat-panel"
         x-cloak>
        
        <!-- Header Section -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <!-- Avatar Admin/Logo -->
                <div class="relative w-10 h-10 rounded-full shadow-sm flex items-center justify-center bg-slate-100 overflow-hidden border border-slate-200 shrink-0">
                    <img :src="adminAvatar || 'https://ui-avatars.com/api/?name=Admin+Fadilah&color=ea580c&background=ffedd5&rounded=true'" 
                         class="w-full h-full object-cover">
                </div>
                
                <div class="text-left">
                    <h3 class="text-xs font-bold text-slate-800">Fadilah Digital Printing</h3>
                    <p class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full" :class="adminOnlineStatus === 'online' ? 'bg-green-500 animate-pulse' : 'bg-slate-400'"></span>
                        <span class="font-semibold text-2xs" :class="adminOnlineStatus === 'online' ? 'text-emerald-600' : 'text-slate-500'" x-html="adminStatusText"></span>
                    </p>
                </div>
            </div>
            
            <!-- Window Control Buttons (Minimize & Close) -->
            <div class="flex items-center gap-2">
                <!-- Minimize Button -->
                <button @click="minimizeChatPanel()" title="Minimize" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors focus:outline-none text-xs font-bold cursor-pointer">
                    —
                </button>
                <!-- Close Button -->
                <button @click="closeChatPanel()" title="Close" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition-colors focus:outline-none text-lg font-medium cursor-pointer">
                    &times;
                </button>
            </div>
        </div>

        @auth
        <!-- Chat History Area for Logged In Customer -->
        <div id="widget-chat-messages-area" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/30">
            
            <!-- Linked Product / Order Snapshot Reference -->
            <div x-show="linkedProduct" class="bg-orange-50/80 border border-orange-200/60 rounded-2xl p-3 flex gap-2.5 items-center justify-between text-[11px] shadow-sm animate-fade-in" x-cloak>
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-200">
                        <img :src="linkedProduct?.image_url" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 text-left">
                        <span class="font-bold text-slate-800 truncate block" x-text="linkedProduct?.name"></span>
                        <span class="text-orange-500 font-bold block text-[10px]" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(linkedProduct?.price)"></span>
                    </div>
                </div>
                <button @click="sendLinkedProduct()" class="btn-xs bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[8px] px-2.5 py-1 rounded-lg flex-shrink-0 cursor-pointer shadow-3xs uppercase tracking-wider">
                    Kirim
                </button>
            </div>

            <div x-show="linkedOrder" class="bg-orange-50/80 border border-orange-200/60 rounded-2xl p-3 flex gap-2.5 items-center justify-between text-[11px] shadow-sm animate-fade-in" x-cloak>
                <div class="min-w-0 text-left">
                    <span class="font-bold text-slate-800 block text-[9px]" x-text="'Invoice: ' + linkedOrder?.invoice"></span>
                    <span class="text-slate-500 text-[10px] block mt-0.5" x-text="'Status: ' + linkedOrder?.status"></span>
                </div>
                <button @click="sendLinkedOrder()" class="btn-xs bg-orange-500 hover:bg-orange-650 text-white font-extrabold text-[8px] px-2.5 py-1 rounded-lg flex-shrink-0 cursor-pointer shadow-3xs uppercase tracking-wider">
                    Kirim
                </button>
            </div>

            <!-- Messages Loop -->
            <template x-for="(m, idx) in messages" :key="m.id">
                <div class="flex flex-col">
                    <!-- Date Separator -->
                    <div x-show="idx === 0 || messages[idx-1].date !== m.date" class="text-center py-2 animate-fade-in">
                        <span class="bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider border border-slate-200/40" x-text="m.date"></span>
                    </div>

                    <!-- Message Bubble Alignment -->
                    <div :class="m.is_mine ? 'justify-end' : 'justify-start'" class="flex items-start gap-2 mt-2 group animate-fade-in">
                        
                        <!-- Avatar on Left (for other sender) -->
                        <div x-show="!m.is_mine" class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center shadow-sm border border-slate-200">
                            <img :src="adminAvatar || 'https://ui-avatars.com/api/?name=Admin+Fadilah&color=ea580c&background=ffedd5&rounded=true'" 
                                 class="w-7 h-7 rounded-full object-cover">
                        </div>

                        <div class="max-w-[75%] space-y-0.5">
                            <div :class="m.is_mine ? 'bg-orange-500 text-white rounded-t-2xl rounded-l-2xl rounded-br-sm' : 'bg-white text-slate-800 border border-slate-200 rounded-t-2xl rounded-r-2xl rounded-bl-sm'" 
                                 class="px-3.5 py-2 text-xs shadow-3xs text-left">
                                
                                <!-- File Attachment -->
                                <template x-if="m.attachment">
                                    <div class="mb-2 p-1.5 bg-slate-50/80 border border-slate-200 rounded-xl flex gap-2 items-center text-slate-800">
                                        <template x-if="m.attachment_type === 'image'">
                                            <a :href="m.attachment.file_url" target="_blank" class="block w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 border border-slate-200 hover:scale-105 transition-transform">
                                                <img :src="m.attachment.file_url" class="w-full h-full object-cover">
                                            </a>
                                        </template>
                                        <template x-if="m.attachment_type !== 'image'">
                                            <div class="w-7 h-7 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                        </template>
                                        <div class="min-w-0 text-left flex-1">
                                            <p class="text-[9px] font-bold truncate" x-text="m.attachment.file_name"></p>
                                            <a :href="m.attachment.file_url" target="_blank" class="text-[8px] text-orange-600 font-extrabold hover:underline block mt-0.5 uppercase tracking-wider">Unduh</a>
                                        </div>
                                    </div>
                                </template>

                                <!-- Text -->
                                <p class="whitespace-pre-line leading-relaxed font-medium" x-html="m.message"></p>
                            </div>

                            <!-- Meta Info (Time + Read State) -->
                            <div class="flex items-center gap-1.5 text-[8px] text-slate-400 font-bold uppercase tracking-wider" :class="m.is_mine ? 'justify-end' : 'justify-start'">
                                <span x-text="m.time"></span>
                                <template x-if="m.is_mine">
                                    <div class="flex items-center gap-0.5">
                                        <span x-show="m.is_read" class="text-orange-500">✔ dibaca</span>
                                        <span x-show="!m.is_read" class="text-slate-300">✔ terkirim</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Typing Animation Bubble -->
            <div x-show="aiTyping" class="flex items-start gap-2 mt-2 animate-fade-in" x-cloak>
                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center shadow-sm border border-slate-200">
                    <img :src="adminAvatar || 'https://ui-avatars.com/api/?name=Admin+Fadilah&color=ea580c&background=ffedd5&rounded=true'" 
                         class="w-7 h-7 rounded-full object-cover">
                </div>
                <div class="max-w-[75%] space-y-0.5">
                    <div class="bubble-other bg-white border border-slate-200 rounded-t-2xl rounded-r-2xl rounded-bl-sm px-3.5 py-2 text-xs shadow-3xs text-left flex items-center gap-1 bg-slate-50/50">
                        <span class="font-semibold text-slate-500 animate-pulse">AI sedang mengetik</span>
                        <span class="flex gap-0.5">
                            <span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                            <span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                            <span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Input Bar Section -->
        <div class="p-3.5 border-t border-slate-200 bg-white space-y-2 flex-shrink-0">
            <!-- Selected File Preview -->
            <div x-show="selectedFile" class="p-2 bg-orange-50/50 border border-orange-200/50 rounded-xl flex items-center justify-between text-[10px]" x-cloak>
                <div class="flex items-center gap-1.5 min-w-0">
                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span class="font-bold text-slate-700 truncate max-w-[180px]" x-text="selectedFile?.name"></span>
                </div>
                <button @click="clearFile()" class="text-red-500 hover:text-red-700 font-bold uppercase tracking-wider text-[9px] cursor-pointer">Batal</button>
            </div>

            <!-- Input Controls Form -->
            <form @submit.prevent="sendWidgetMessage()" class="flex items-center gap-2">
                <!-- File Input Attachment -->
                <div>
                    <input type="file" id="widget-chat-file-input" @change="handleFileSelect($event)" class="hidden">
                    <button type="button" @click="document.getElementById('widget-chat-file-input').click()"
                            class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-xl transition-colors cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>
                </div>

                <!-- Emoji Picker -->
                <div class="relative shrink-0" x-data="{ emojiOpen: false }">
                    <button type="button" @click="emojiOpen = !emojiOpen"
                            class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-xl transition-colors cursor-pointer text-xs">
                        😊
                    </button>
                    <div x-show="emojiOpen" @click.away="emojiOpen = false" x-cloak
                         class="absolute bottom-full mb-2 left-0 bg-white border border-slate-200 rounded-xl p-1.5 shadow-xl flex gap-1 z-50">
                        <template x-for="emoji in ['😊','👍','😂','❤️','🙏','😮','😭','🔥']" :key="emoji">
                            <button type="button" @click="typedMessage += emoji; emojiOpen = false" class="hover:scale-125 transition-transform cursor-pointer text-sm">
                                <span x-text="emoji"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Textarea Message Box -->
                <textarea x-model="typedMessage" 
                          @keydown="handleKeyDown($event)"
                          placeholder="Ketik pesan..." 
                          class="flex-1 bg-slate-50 hover:bg-slate-100/75 focus:bg-white border border-slate-200 focus:border-orange-500 rounded-xl px-3 py-2 text-xs focus:outline-none resize-none no-scrollbar h-8 max-h-16 leading-relaxed transition-all"></textarea>

                <!-- Send Action Button -->
                <button type="submit"
                        class="bg-orange-500 hover:bg-orange-650 text-white font-extrabold px-3.5 py-2.5 rounded-xl text-3xs transition-colors flex-shrink-0 shadow-md uppercase tracking-wider cursor-pointer">
                    Kirim
                </button>
            </form>
        </div>
        @else
        <!-- Guest State Layout (Locked/Login Required) -->
        <div class="flex-grow flex flex-col items-center justify-center p-6 text-center space-y-4 bg-slate-50/50">
            <div class="w-16 h-16 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center text-2xl shadow-inner animate-pulse">🔒</div>
            <div>
                <h4 class="text-xs font-bold text-slate-800">Mulai Chat Bantuan</h4>
                <p class="text-3xs text-slate-500 leading-relaxed max-w-[220px] mx-auto mt-1">Silakan masuk ke akun Anda terlebih dahulu untuk memulai percakapan langsung dengan customer service kami.</p>
            </div>
            <a href="{{ route('login') }}" class="inline-block btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-3xs px-6 py-2.5 shadow-md uppercase tracking-wider transition-transform hover:scale-105 active:scale-95">
                Masuk ke Akun
            </a>
        </div>
        @endauth
    </div>
</div>

<script>
    // Session flash data from server (controller redirect)
    @if(session('open_chat'))
        window.__chatOpenOnLoad = true;
    @endif
    @if(session('chat_linked_product'))
        window.__chatLinkedProduct = @json(session('chat_linked_product'));
    @endif
    @if(session('chat_linked_order'))
        window.__chatLinkedOrder = @json(session('chat_linked_order'));
    @endif
</script>

<script>
    function chatWidget() {
        return {
            isOpen: false,
            unreadCount: 0,
            messages: [],
            typedMessage: '',
            selectedFile: null,
            adminAvatar: '',
            adminOnlineStatus: 'offline',
            adminStatusText: 'Offline',
            aiTyping: false,
            pollMessagesInterval: null,
            pollUnreadInterval: null,
            isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},

            linkedProduct: null,
            linkedOrder: null,

            initChatWidget() {
                // Read initial open/close state from LocalStorage
                this.isOpen = localStorage.getItem('chat_widget_open') === 'true';

                // Auto-open from session flash (e.g., when /chat route redirects here)
                if (window.__chatOpenOnLoad) {
                    this.openChatPanel();
                }

                // Listen to open widget event (from sidebar button, product detail button, etc.)
                window.addEventListener('open-chat-widget', () => {
                    this.openChatPanel();
                });

                if (this.isLoggedIn) {
                    // Check for linked product from session flash (controller redirect)
                    if (window.__chatLinkedProduct) {
                        try {
                            this.linkedProduct = JSON.parse(window.__chatLinkedProduct);
                            this.openChatPanel();
                        } catch (e) { /* ignore parse error */ }
                    }

                    // Check for linked order from session flash (controller redirect)
                    if (window.__chatLinkedOrder) {
                        try {
                            this.linkedOrder = JSON.parse(window.__chatLinkedOrder);
                            this.openChatPanel();
                        } catch (e) { /* ignore parse error */ }
                    }

                    // Check for linked product/order in localStorage (legacy support)
                    const storedProduct = localStorage.getItem('chat_linked_product');
                    if (storedProduct) {
                        this.linkedProduct = JSON.parse(storedProduct);
                        localStorage.removeItem('chat_linked_product');
                        this.openChatPanel();
                    }

                    const storedOrder = localStorage.getItem('chat_linked_order');
                    if (storedOrder) {
                        this.linkedOrder = JSON.parse(storedOrder);
                        localStorage.removeItem('chat_linked_order');
                        this.openChatPanel();
                    }

                    // If window.currentLinkedProduct is set (from product details page), read it
                    if (window.currentLinkedProduct) {
                        this.linkedProduct = window.currentLinkedProduct;
                        // Don't auto-open here; user clicked the chat button which dispatches the event
                    }

                    // Initial poll
                    this.pollUnreadCount();

                    // Set up intervals: continuous sync even when widget is closed/minimized
                    this.pollUnreadInterval = setInterval(() => {
                        this.pollUnreadCount();
                    }, 10000); // 10s poll for unread badge

                    this.pollMessagesInterval = setInterval(() => {
                        this.pollMessages();
                    }, 3000); // 3s message sync always running in background

                    this.pollMessages(true);
                }
            },

            openChatPanel() {
                this.isOpen = true;
                this.unreadCount = 0;
                localStorage.setItem('chat_widget_open', 'true');
                if (this.isLoggedIn) {
                    this.pollMessages(true);
                }
            },

            minimizeChatPanel() {
                this.isOpen = false;
                localStorage.setItem('chat_widget_open', 'false');
            },

            closeChatPanel() {
                this.isOpen = false;
                localStorage.setItem('chat_widget_open', 'false');
            },

            pollUnreadCount() {
                if (!this.isLoggedIn) return;
                fetch('{{ route('chat.unread_badge') }}')
                    .then(res => res.json())
                    .then(data => {
                        this.unreadCount = data.unread;
                    })
                    .catch(err => console.error("Error fetching unread count:", err));
            },

            pollMessages(forceScroll = false) {
                if (!this.isLoggedIn) return;
                fetch('{{ route('chat.poll') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (res.status === 401) {
                        clearInterval(this.pollMessagesInterval);
                        clearInterval(this.pollUnreadInterval);
                        return null;
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data) return;
                    
                    this.adminAvatar = data.room?.admin_avatar || '';
                    this.adminOnlineStatus = data.room?.admin_status || 'offline';
                    this.adminStatusText = data.room?.admin_status_text || 'Offline';
                    
                    const oldLength = this.messages.length;
                    this.messages = data.messages || [];

                    if (forceScroll || this.messages.length > oldLength) {
                        this.scrollToBottom();
                    }
                })
                .catch(err => console.error("Poll messages error:", err));
            },

            sendWidgetMessage() {
                if (!this.isLoggedIn) return;
                if (this.typedMessage.trim() === '' && !this.selectedFile) return;

                const formData = new FormData();
                formData.append('message', this.typedMessage);
                if (this.selectedFile) {
                    formData.append('attachment', this.selectedFile);
                }

                this.aiTyping = true;
                this.typedMessage = '';
                this.clearFile();

                fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.pollMessages(true);
                        setTimeout(() => {
                            this.aiTyping = false;
                            this.pollMessages(true);
                        }, 1000);
                    } else {
                        this.aiTyping = false;
                        alert(data.error || "Gagal mengirim pesan.");
                    }
                })
                .catch(err => {
                    this.aiTyping = false;
                    console.error("Error sending message:", err);
                });
            },

            handleKeyDown(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendWidgetMessage();
                }
            },

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.selectedFile = file;
                }
            },

            clearFile() {
                this.selectedFile = null;
                const fileInput = document.getElementById('widget-chat-file-input');
                if (fileInput) fileInput.value = '';
            },

            sendLinkedProduct() {
                if (!this.linkedProduct) return;
                this.typedMessage = `[Rujukan Produk]\nNama: ${this.linkedProduct.name}\nHarga: Rp ${new Intl.NumberFormat('id-ID').format(this.linkedProduct.price)}`;
                this.sendWidgetMessage();
                this.linkedProduct = null;
                
                // Strip URL params
                const url = new URL(window.location);
                url.searchParams.delete('product_id');
                window.history.pushState({}, '', url);
            },

            sendLinkedOrder() {
                if (!this.linkedOrder) return;
                this.typedMessage = `[Rujukan Invoice]\nNomor Invoice: ${this.linkedOrder.invoice}\nStatus: ${this.linkedOrder.status}`;
                this.sendWidgetMessage();
                this.linkedOrder = null;
                
                // Strip URL params
                const url = new URL(window.location);
                url.searchParams.delete('order_id');
                window.history.pushState({}, '', url);
            },

            fetchLinkedProduct(id) {
                if (!this.isLoggedIn) return;
                fetch(`/produk/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.produk) {
                        this.linkedProduct = {
                            id: data.produk.id,
                            name: data.produk.product_name,
                            price: data.produk.price,
                            image_url: data.produk.image ? '/storage/' + data.produk.image : 'https://placehold.co/100'
                        };
                        this.openChatPanel();
                    }
                })
                .catch(err => console.error("Error fetching linked product:", err));
            },

            fetchLinkedOrder(id) {
                if (!this.isLoggedIn) return;
                this.linkedOrder = {
                    id: id,
                    invoice: 'Invoice #' + id,
                    status: 'Menunggu'
                };
                this.openChatPanel();
            },

            scrollToBottom() {
                setTimeout(() => {
                    const area = document.getElementById('widget-chat-messages-area');
                    if (area) {
                        area.scrollTop = area.scrollHeight;
                    }
                }, 100);
            }
        };
    }
</script>
