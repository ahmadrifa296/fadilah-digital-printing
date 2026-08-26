@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pesan Masuk (Feedback)</h1>
            <p class="mt-2 text-sm text-gray-600">Daftar pertanyaan, keluhan, dan saran yang dikirim pelanggan melalui formulir Kontak Kami.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Kotak Masuk Kontak Pelanggan</h2>
            </div>

            @if($feedbacks->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Kotak masuk kosong</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada pesan yang dikirimkan oleh pengunjung website Anda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Pengirim</th>
                                <th class="p-4">Subjek & Pesan</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @foreach($feedbacks as $fb)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ !$fb->is_read ? 'bg-indigo-50/30 font-medium' : '' }}">
                                    <td class="p-4 whitespace-nowrap text-gray-500 text-xs">
                                        {{ $fb->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">{{ $fb->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $fb->email }}</div>
                                    </td>
                                    <td class="p-4 max-w-md">
                                        <div class="text-gray-900 font-semibold mb-1">{{ $fb->subject }}</div>
                                        <p class="text-xs text-gray-500 leading-relaxed whitespace-pre-line">{{ $fb->message }}</p>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        @if(!$fb->is_read)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                Baru
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600">
                                                Dibaca
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-right space-x-2">
                                        @if(!$fb->is_read)
                                            <form action="{{ route('feedbacks.read', $fb->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                                                    Tandai Dibaca
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('feedbacks.destroy', $fb->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pesan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
