<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Fadilah Printing') }} — Autentikasi</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Global font override */
        * { font-family: 'Inter', sans-serif; }

        /* Alpine x-cloak fallback */
        [x-cloak] { display: none !important; }

        /* Smooth animation definitions */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes blobFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-12px) scale(1.05); }
        }
    </style>
</head>
<body class="h-full antialiased bg-slate-100 text-slate-900 selection:bg-orange-500 selection:text-white">

<div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-slate-100">

    {{-- ── Background Ambient Glows ── --}}
    <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-orange-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-slate-300/50 blur-[120px] pointer-events-none"></div>

    {{-- ── Unified Centered Card Container ─────────────────── --}}
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border border-slate-200/80 relative z-10 my-auto"
         style="animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;">
        {{ $slot }}
    </div>

</div>

</body>
</html>