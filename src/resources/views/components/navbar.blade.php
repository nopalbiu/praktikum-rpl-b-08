@php
    $links = [
        ['label' => 'Katalog', 'route' => 'home', 'active' => request()->routeIs('home', 'catalog.index', 'product.show')],
        ['label' => 'Tentang', 'route' => 'about', 'active' => request()->routeIs('about')],
    ];
    $user = auth()->user();
    $isAdmin = $user && (int) $user->id_role === 1;
@endphp

<header x-data="{ open: false }" @keydown.escape.window="open = false"
        class="sticky top-0 z-50 border-b border-zinc-800/60 bg-zinc-950/85 backdrop-blur-md">
    <nav class="container-page flex h-16 items-center justify-between gap-4" aria-label="Navigasi utama">
        {{-- Kiri: logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="WearWoreWorn — beranda">
            <img src="{{ asset('images/logo-www.png') }}" alt="WearWoreWorn" class="h-8 w-auto">
        </a>

        {{-- Tengah: tautan (desktop) --}}
        <div class="hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="nav-link {{ $link['active'] ? 'nav-link-active' : '' }}"
                   @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
        </div>

        {{-- Kanan: keranjang + akun --}}
        <div class="flex items-center gap-1 sm:gap-2">
            <a href="{{ route('cart.index') }}"
               class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-zinc-300 transition-colors hover:bg-zinc-800/60 hover:text-white {{ request()->routeIs('cart.*') ? 'text-white' : '' }}"
               aria-label="Keranjang{{ $cartCount ? " ($cartCount barang)" : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
                @if ($cartCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[11px] font-semibold text-white">
                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                    </span>
                @endif
            </a>

            @auth
                <div x-data="{ menu: false }" class="relative hidden md:block" @click.outside="menu = false">
                    <button type="button" @click="menu = !menu" :aria-expanded="menu"
                            class="flex items-center gap-2 rounded-lg py-1.5 pr-2 pl-1.5 text-sm text-zinc-300 transition-colors hover:bg-zinc-800/60 hover:text-white">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-800 text-xs font-semibold text-white">
                            {{ strtoupper(mb_substr($user->nama ?? 'U', 0, 1)) }}
                        </span>
                        <span class="max-w-32 truncate">{{ $user->nama }}</span>
                        <svg class="h-4 w-4 text-zinc-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                    </button>

                    <div x-cloak x-show="menu" x-transition.origin.top.right
                         class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 py-1 shadow-xl shadow-black/40">
                        @if ($isAdmin)
                            <a href="{{ route('admin.product.index') }}" class="block px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-white">Panel admin</a>
                        @endif
                        <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-white">Profil saya</a>
                        <a href="{{ route('user.profile') }}#pesanan" class="block px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-white">Pesanan saya</a>
                        <div class="my-1 border-t border-zinc-800"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-300 hover:bg-zinc-800">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden md:inline-flex">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden md:inline-flex">Daftar</a>
            @endauth

            {{-- Tombol menu (mobile) --}}
            <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="menu-mobile"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-zinc-300 hover:bg-zinc-800/60 hover:text-white md:hidden">
                <span class="sr-only">Buka menu</span>
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-cloak x-show="open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </nav>

    {{-- Panel menu (mobile) --}}
    <div id="menu-mobile" x-cloak x-show="open" x-transition.opacity.duration.150ms
         class="border-t border-zinc-800/60 bg-zinc-950 md:hidden">
        <div class="container-page flex flex-col gap-1 py-4">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="rounded-lg px-3 py-2.5 text-base font-medium {{ $link['active'] ? 'bg-zinc-900 text-white' : 'text-zinc-300 hover:bg-zinc-900' }}">{{ $link['label'] }}</a>
            @endforeach

            <div class="my-2 border-t border-zinc-800/60"></div>

            @auth
                <div class="px-3 pb-2 text-sm text-zinc-500">Masuk sebagai <span class="text-zinc-300">{{ $user->nama }}</span></div>
                @if ($isAdmin)
                    <a href="{{ route('admin.product.index') }}" class="rounded-lg px-3 py-2.5 text-base text-zinc-300 hover:bg-zinc-900">Panel admin</a>
                @endif
                <a href="{{ route('user.profile') }}" class="rounded-lg px-3 py-2.5 text-base text-zinc-300 hover:bg-zinc-900">Profil saya</a>
                <a href="{{ route('user.profile') }}#pesanan" class="rounded-lg px-3 py-2.5 text-base text-zinc-300 hover:bg-zinc-900">Pesanan saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2.5 text-left text-base text-red-300 hover:bg-zinc-900">Keluar</button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</header>
