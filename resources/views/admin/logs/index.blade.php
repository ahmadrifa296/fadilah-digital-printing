@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Audit Log Aktivitas</h1>
            <p class="mt-2 text-sm text-gray-600">Catatan log aktivitas lengkap pengguna dan aksi sistem untuk keamanan dan pemeliharaan.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Log Keamanan & Aktivitas</h2>
                <span class="text-xs font-semibold text-gray-500">Menampilkan hingga 50 entri terbaru per halaman</span>
            </div>

            @if($logs->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Log masih kosong</h3>
                    <p class="mt-1 text-sm text-gray-500">Aktivitas sistem akan dicatat secara otomatis oleh middleware pelacak.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="p-4">Waktu</th>
                                <th class="p-4">Pengguna</th>
                                <th class="p-4">Aktivitas</th>
                                <th class="p-4">Deskripsi Rinci</th>
                                <th class="p-4">IP Address & User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="p-4 whitespace-nowrap text-gray-500 text-xs">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        @if($log->user)
                                            <div class="font-bold text-gray-900">{{ $log->user->name }}</div>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $log->user->role === 'admin' || $log->user->role === 'owner' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($log->user->role) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-medium text-xs">Sistem / Pengunjung</span>
                                        @endif
                                    </td>
                                    <td class="p-4 whitespace-nowrap font-bold text-gray-900">
                                        {{ $log->activity }}
                                    </td>
                                    <td class="p-4 max-w-sm">
                                        <p class="text-xs text-gray-600 leading-relaxed">{{ $log->description }}</p>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500 max-w-xs truncate">
                                        <div class="font-semibold text-gray-700">{{ $log->ip_address }}</div>
                                        <div class="truncate mt-0.5" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection
