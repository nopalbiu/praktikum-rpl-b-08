<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'WearWoreWorn — koleksi streetwear untuk sehari-hari.')">
    <meta name="theme-color" content="#09090b">

    {{-- Splash logo cukup sekali per sesi: tandai sebelum halaman digambar supaya tidak berkedip --}}
    <script>
        try { if (sessionStorage.getItem('www-splash-seen')) document.documentElement.classList.add('no-splash'); }
        catch (e) { document.documentElement.classList.add('no-splash'); }
    </script>

    <title>@hasSection('title')@yield('title') · WearWoreWorn @else WearWoreWorn @endif</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="@yield('body-class', 'flex min-h-screen flex-col font-sans')">
    <div id="page-progress" aria-hidden="true"></div>

    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-zinc-950">Langsung ke konten</a>

    @yield('body')

    <x-toast />
    <x-confirm-dialog />
    @stack('scripts')
</body>
</html>
