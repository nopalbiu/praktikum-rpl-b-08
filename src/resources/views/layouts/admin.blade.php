@extends('layouts.base')

@php
    $adminLinks = [
        ['label' => 'Produk', 'route' => 'admin.product.index', 'active' => request()->routeIs('admin.product.*'),
         'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
        ['label' => 'Pesanan', 'route' => 'admin.orders.index', 'active' => request()->routeIs('admin.orders.*'),
         'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
    ];
@endphp

@section('body')
    <header class="sticky top-0 z-40 border-b border-zinc-800/60 bg-zinc-950/85 backdrop-blur-md">
        <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.product.index') }}" aria-label="Panel admin">
                    <img src="{{ asset('images/logo-www.png') }}" alt="WearWoreWorn" class="h-8 w-auto">
                </a>
                <span class="badge badge-info">Admin</span>
            </div>

            <nav class="flex items-center gap-1" aria-label="Navigasi admin">
                @foreach ($adminLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $link['active'] ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-white' }}"
                       @if ($link['active']) aria-current="page" @endif>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                        <span class="hidden sm:inline">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex" target="_blank" rel="noopener">Lihat toko</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <div id="konten" class="flex-1">
        @yield('content')
    </div>
@endsection
