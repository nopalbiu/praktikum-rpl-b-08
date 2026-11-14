<footer class="mt-20 border-t border-zinc-800/60">
    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <img src="{{ asset('images/logo-www.png') }}" alt="WearWoreWorn" class="h-7 w-auto opacity-80">
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-zinc-500">
                Streetwear esensial untuk dipakai setiap hari. Nyaman, awet, dan gampang dipadukan.
            </p>
        </div>

        <div>
            <h2 class="text-xs font-semibold tracking-wider text-zinc-400 uppercase">Belanja</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('home') }}" class="text-zinc-500 transition-colors hover:text-white">Katalog</a></li>
                <li><a href="{{ route('cart.index') }}" class="text-zinc-500 transition-colors hover:text-white">Keranjang</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-xs font-semibold tracking-wider text-zinc-400 uppercase">Informasi</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('about') }}" class="text-zinc-500 transition-colors hover:text-white">Tentang kami</a></li>
                @auth
                    <li><a href="{{ route('user.profile') }}" class="text-zinc-500 transition-colors hover:text-white">Akun saya</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="text-zinc-500 transition-colors hover:text-white">Masuk</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="border-t border-zinc-800/60">
        <p class="container-page py-6 text-xs text-zinc-600">&copy; {{ date('Y') }} WearWoreWorn. Hak cipta dilindungi.</p>
    </div>
</footer>
