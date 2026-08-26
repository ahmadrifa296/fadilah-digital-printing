<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title text-slate-800">📊 Dashboard CRM & Monitoring Owner</h1>
        <p class="page-subtitle text-slate-500">Memonitor performa pelayanan CS secara real-time</p>
    </x-slot>

    <!-- Grid Statistik Analitik -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 text-slate-700">
        
        <!-- Total Chat -->
        <div class="bg-white p-5 border border-slate-200 rounded-2xl flex items-center gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wide">Total Chat</span>
                <span class="text-xl font-extrabold text-slate-800">{{ $totalChat }}</span>
            </div>
        </div>

        <!-- Chat Aktif -->
        <div class="bg-white p-5 border border-slate-200 rounded-2xl flex items-center gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">O</span>
            </div>
            <div>
                <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wide">Chat Aktif (Open)</span>
                <span class="text-xl font-extrabold text-slate-800">{{ $activeChat }}</span>
            </div>
        </div>

        <!-- Chat Selesai -->
        <div class="bg-white p-5 border border-slate-200 rounded-2xl flex items-center gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                <span class="font-bold text-lg">C</span>
            </div>
            <div>
                <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wide">Chat Selesai (Closed)</span>
                <span class="text-xl font-extrabold text-slate-800">{{ $closedChat }}</span>
            </div>
        </div>

        <!-- Customer Aktif -->
        <div class="bg-white p-5 border border-slate-200 rounded-2xl flex items-center gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wide">Customer Aktif (7d)</span>
                <span class="text-xl font-extrabold text-slate-800">{{ $activeCustomers }}</span>
            </div>
        </div>

    </div>

    <!-- Live Monitor Percakapan -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs text-slate-700"
         x-data="{
             selectedRoomId: null,
             messages: [],
             customerName: '',

             viewChat(roomId, name) {
                 this.selectedRoomId = roomId;
                 this.customerName = name;
                 fetch(`/admin/chat/${roomId}/poll`, {
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 })
                     .then(res => {
                         if (res.status === 401) {
                             return null;
                         }
                         return res.json();
                     })
                     .then(data => {
                         if (!data) return;
                         this.messages = data.messages || [];
                     });
             }
         }">
        
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">Live Monitoring Percakapan Customer</h3>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- List Chat Rooms -->
            <div class="lg:col-span-1 border border-slate-200 rounded-xl divide-y divide-slate-100 h-96 overflow-y-auto bg-slate-50/50">
                @foreach($rooms as $r)
                    <div @click="viewChat({{ $r->id }}, '{{ $r->customer->name }}')"
                         :class="selectedRoomId === {{ $r->id }} ? 'bg-orange-50/70 border-l-4 border-orange-500' : 'hover:bg-slate-100 cursor-pointer'"
                         class="p-4 flex gap-3 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs uppercase text-slate-600 flex-shrink-0">
                            {{ substr($r->customer->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800 truncate">{{ $r->customer->name }}</h4>
                                <span class="text-[9px] text-slate-400 capitalize">{{ $r->status }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $r->lastMessage ? ($r->lastMessage->message ?: '[Attachment]') : 'Belum ada pesan' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Read-only Chat Area Monitor -->
            <div class="lg:col-span-2 border border-slate-200 rounded-xl flex flex-col h-96 justify-between bg-slate-50">
                
                <template x-if="selectedRoomId">
                    <div class="flex-1 flex flex-col justify-between overflow-hidden">
                        <div class="p-3 bg-white border-b border-slate-200 flex items-center gap-2 flex-shrink-0">
                            <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></span>
                            <h4 class="text-xs font-bold text-slate-800" x-text="'Memonitor Percakapan: ' + customerName"></h4>
                            <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[8px] font-bold">MONITOR MODE (READ-ONLY)</span>
                        </div>

                        <!-- Chat Messages area -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <template x-for="m in messages" :key="m.id">
                                <div :class="m.is_mine ? 'justify-end' : 'justify-start'" class="flex mt-2">
                                    <div class="max-w-[70%]">
                                        <div :class="m.sender_role === 'admin' ? 'bg-slate-200 text-slate-800 rounded-2xl px-3 py-2 text-xs border border-slate-300/50 shadow-2xs' : 'bg-orange-50 text-orange-950 rounded-2xl px-3 py-2 text-xs border border-orange-100 shadow-2xs'" class="text-left">
                                            
                                            <!-- Attachment rendering -->
                                            <template x-if="m.attachment">
                                                <div class="mb-1.5 p-1.5 bg-white/50 border border-slate-200 rounded flex gap-2 items-center">
                                                    <div class="w-6 h-6 bg-orange-100 text-orange-600 rounded flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] font-bold text-slate-800 truncate" x-text="m.attachment.file_name"></p>
                                                        <a :href="m.attachment.file_url" target="_blank" class="text-[9px] text-orange-600 font-bold hover:underline block mt-0.5">Lihat Berkas</a>
                                                    </div>
                                                </div>
                                            </template>

                                            <span class="block font-bold text-[9px] mb-0.5 text-slate-500 uppercase" x-text="m.sender_name + ' (' + m.sender_role + ')'"></span>
                                            <p class="whitespace-pre-line leading-relaxed" x-text="m.message"></p>
                                        </div>
                                        <span class="text-[8px] text-slate-400 block text-right mt-0.5" x-text="m.time"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!selectedRoomId">
                    <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8">
                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <p class="text-xs">Pilih percakapan di samping untuk memonitor isi chat.</p>
                    </div>
                </template>

            </div>

        </div>

    </div>
</x-app-layout>
