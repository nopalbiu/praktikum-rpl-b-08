@extends('layouts.app')

@section('title', 'Akun saya')

@php
    use App\Models\Order;
    use App\Support\ImageUrl;

    $flow = [
        Order::STATUS_MENUNGGU_PEMBAYARAN => 'Dibuat',
        Order::STATUS_SUDAH_DIBAYAR => 'Dibayar',
        Order::STATUS_DALAM_PERJALANAN => 'Dikirim',
        Order::STATUS_SELESAI => 'Selesai',
    ];
    $flowKeys = array_keys($flow);
    $activeOrders = $orders->where('status_pesanan', Order::STATUS_MENUNGGU_PEMBAYARAN)->count();
    $passwordErrors = $errors->getBag('password');
@endphp

@section('content')
    <main class="container-page max-w-4xl pt-8 pb-12 sm:pt-12"
          x-data="{ tab: location.hash === '#pesanan' ? 'orders' : 'profile' }"
          x-init="$watch('tab', t => history.replaceState(null, '', t === 'orders' ? '#pesanan' : location.pathname))"
          @hashchange.window="if (location.hash === '#pesanan') tab = 'orders'">

        <div class="mb-8 flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-800 text-xl font-semibold text-white">
                {{ strtoupper(mb_substr($user->nama ?? 'U', 0, 1)) }}
            </span>
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-bold tracking-tight text-white">{{ $user->nama }}</h1>
                <p class="truncate text-sm text-zinc-500">{{ $user->email }}</p>
            </div>
        </div>

        {{-- Tab --}}
        <div class="mb-8 flex gap-1 border-b border-zinc-800" role="tablist">
            <button type="button" role="tab" @click="tab = 'profile'" :aria-selected="tab === 'profile'"
                    :class="tab === 'profile' ? 'border-white text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300'"
                    class="-mb-px border-b-2 px-4 py-3 text-sm font-medium transition-colors">Profil</button>
            <button type="button" role="tab" @click="tab = 'orders'" :aria-selected="tab === 'orders'"
                    :class="tab === 'orders' ? 'border-white text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300'"
                    class="-mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors">
                Pesanan
                @if ($activeOrders)
                    <span class="rounded-full bg-amber-500/20 px-1.5 text-[11px] text-amber-300">{{ $activeOrders }}</span>
                @endif
            </button>
        </div>

        {{-- Tab profil --}}
        <div x-show="tab === 'profile'" role="tabpanel" class="space-y-6">
            <section class="card p-5 sm:p-6">
                <h2 class="text-base font-semibold text-white">Data diri</h2>
                <form action="{{ route('user.profile.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nama" class="label">Nama lengkap</label>
                            <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" required autocomplete="name"
                                   class="input @error('nama') input-error @enderror">
                            @error('nama') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input type="email" id="email" value="{{ $user->email }}" readonly class="input cursor-not-allowed text-zinc-500">
                            <p class="mt-1.5 text-xs text-zinc-500">Email tidak bisa diubah.</p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </section>

            <section class="card p-5 sm:p-6" x-data="{ open: {{ $passwordErrors->any() ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-white">Password</h2>
                        <p class="mt-1 text-sm text-zinc-500">Ganti password secara berkala supaya akun tetap aman.</p>
                    </div>
                    <button type="button" x-show="!open" @click="open = true" class="btn btn-secondary btn-sm">Ganti password</button>
                </div>

                <form x-cloak x-show="open" action="{{ route('user.profile.password') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="max-w-sm">
                        <label for="current_password" class="label">Password saat ini</label>
                        <x-password-input name="current_password" required />
                        @if ($passwordErrors->has('current_password')) <p class="field-error">{{ $passwordErrors->first('current_password') }}</p> @endif
                    </div>
                    <div class="grid max-w-2xl gap-4 sm:grid-cols-2">
                        <div>
                            <label for="new_password" class="label">Password baru</label>
                            <x-password-input name="password" id="new_password" autocomplete="new-password" required minlength="8" />
                            @if ($passwordErrors->has('password')) <p class="field-error">{{ $passwordErrors->first('password') }}</p> @else <p class="mt-1.5 text-xs text-zinc-500">Minimal 8 karakter.</p> @endif
                        </div>
                        <div>
                            <label for="password_confirmation" class="label">Ulangi password baru</label>
                            <x-password-input name="password_confirmation" autocomplete="new-password" required />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan password</button>
                        <button type="button" @click="open = false" class="btn btn-ghost">Batal</button>
                    </div>
                </form>
            </section>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Keluar dari akun</button>
            </form>
        </div>

        {{-- Tab pesanan --}}
        <div x-cloak x-show="tab === 'orders'" role="tabpanel">
            @if ($orders->isEmpty())
                <x-empty-state title="Belum ada pesanan" icon="receipt">
                    Pesanan yang kamu buat akan muncul di sini.
                    <x-slot:action>
                        <a href="{{ route('home') }}" class="btn btn-primary">Mulai belanja</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <ul class="space-y-4">
                    @foreach ($orders as $order)
                        @php
                            $code = '#TRX-'.str_pad($order->id_order, 4, '0', STR_PAD_LEFT);
                            $step = array_search($order->status_pesanan, $flowKeys, true);
                            $isCancelled = $order->status_pesanan === Order::STATUS_DIBATALKAN;
                            $isWaiting = $order->status_pesanan === Order::STATUS_MENUNGGU_PEMBAYARAN;
                            $subtotal = $order->items->sum(fn ($i) => $i->harga_satuan * $i->qty);
                            $address = preg_match('/Nama:\s*(.*?)\s*No\. HP:\s*(.*?)\s*Alamat:\s*(.*)/is', $order->alamat_pengiriman, $m)
                                ? ['nama' => $m[1], 'hp' => $m[2], 'alamat' => $m[3]]
                                : ['nama' => null, 'hp' => null, 'alamat' => $order->alamat_pengiriman];
                        @endphp
                        <li class="card overflow-hidden" x-data="{ open: false }">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="font-mono font-medium text-white">{{ $code }}</span>
                                    <span class="text-zinc-500">{{ $order->tanggal_order?->translatedFormat('d M Y') }}</span>
                                </div>
                                <x-status-badge :order="$order" />
                            </div>

                            <div class="flex items-center gap-4 px-5 py-4">
                                <div class="flex -space-x-3">
                                    @foreach ($order->items->take(3) as $item)
                                        @php $img = $item->variant?->product?->images->firstWhere('is_primary', 1) ?? $item->variant?->product?->images->first(); @endphp
                                        <img src="{{ ImageUrl::make($img?->url_gambar, 100) }}" alt="" loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                                             class="h-12 w-12 rounded-lg border-2 border-zinc-950 bg-zinc-900 object-cover">
                                    @endforeach
                                </div>
                                <div class="min-w-0 flex-1 text-sm">
                                    <p class="line-clamp-1 text-zinc-300">{{ $order->items->map(fn ($i) => $i->variant?->product?->nama_product ?? 'Produk')->join(', ') }}</p>
                                    <p class="text-zinc-500">{{ $order->items->sum('qty') }} barang · {{ $order->kurir }}</p>
                                </div>
                                <p class="text-right text-sm font-semibold whitespace-nowrap text-white">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                            </div>

                            <div class="flex flex-wrap gap-2 px-5 pb-4">
                                @if ($isWaiting)
                                    <a href="{{ route('checkout.success', $order->id_order) }}" class="btn btn-primary btn-sm">Bayar sekarang</a>
                                @endif
                                <button type="button" @click="open = !open" :aria-expanded="open" class="btn btn-secondary btn-sm">
                                    <span x-text="open ? 'Tutup detail' : 'Lihat detail'">Lihat detail</span>
                                </button>
                            </div>

                            <div x-cloak x-show="open" x-transition.opacity class="border-t border-zinc-800 bg-zinc-950/40 px-5 py-5">
                                {{-- Progres --}}
                                @if ($isCancelled)
                                    <p class="mb-5 rounded-lg border border-red-900/50 bg-red-950/20 px-3.5 py-2.5 text-sm text-red-300">Pesanan ini dibatalkan.</p>
                                @else
                                    <ol class="mb-6 grid grid-cols-4 gap-2">
                                        @foreach ($flow as $key => $label)
                                            @php $done = $step !== false && $loop->index <= $step; @endphp
                                            <li class="flex flex-col gap-2">
                                                <span class="h-1 rounded-full {{ $done ? 'bg-white' : 'bg-zinc-800' }}"></span>
                                                <span class="text-xs {{ $done ? 'text-zinc-200' : 'text-zinc-600' }}">{{ $label }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif

                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div>
                                        <h3 class="text-xs font-semibold tracking-wider text-zinc-500 uppercase">Barang</h3>
                                        <ul class="mt-3 space-y-2 text-sm">
                                            @foreach ($order->items as $item)
                                                <li class="flex justify-between gap-3">
                                                    <span class="text-zinc-300">
                                                        {{ $item->variant?->product?->nama_product ?? 'Produk' }}
                                                        <span class="text-zinc-500">· {{ $item->variant?->size?->nama_size ?? '-' }} × {{ $item->qty }}</span>
                                                    </span>
                                                    <span class="whitespace-nowrap text-zinc-200">Rp {{ number_format($item->harga_satuan * $item->qty, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <dl class="mt-4 space-y-1.5 border-t border-zinc-800 pt-3 text-sm">
                                            <div class="flex justify-between text-zinc-400"><dt>Subtotal</dt><dd>Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                                            <div class="flex justify-between text-zinc-400"><dt>Ongkir + asuransi</dt><dd>Rp {{ number_format(max(0, $order->total_harga - $subtotal), 0, ',', '.') }}</dd></div>
                                            <div class="flex justify-between font-medium text-white"><dt>Total</dt><dd>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</dd></div>
                                        </dl>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-semibold tracking-wider text-zinc-500 uppercase">Pengiriman</h3>
                                        <div class="mt-3 space-y-1 text-sm">
                                            @if ($address['nama'])
                                                <p class="text-zinc-200">{{ $address['nama'] }} · {{ $address['hp'] }}</p>
                                            @endif
                                            <p class="whitespace-pre-line text-zinc-400">{{ $address['alamat'] }}</p>
                                            <p class="pt-2 text-zinc-500">Kurir: <span class="text-zinc-300">{{ $order->kurir }}</span></p>
                                            @if ($order->payment)
                                                <p class="text-zinc-500">Pembayaran: <span class="text-zinc-300">{{ $order->payment->metode_pembayaran }}</span></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </main>
@endsection
