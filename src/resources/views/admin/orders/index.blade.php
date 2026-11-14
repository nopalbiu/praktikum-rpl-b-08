@extends('layouts.admin')

@section('title', 'Pesanan')

@php
    $currentStatus = request('status', '');
    $allCount = $statusCounts->sum();
    $tabUrl = fn ($status) => route('admin.orders.index', array_filter([
        'status' => $status ?: null,
        'q' => request('q'),
        'sort_id' => request('sort_id'),
    ]));
@endphp

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Pesanan" :subtitle="$allCount.' pesanan masuk'">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="flex w-full flex-wrap gap-2 sm:w-auto" role="search">
                @if ($currentStatus) <input type="hidden" name="status" value="{{ $currentStatus }}"> @endif
                <div class="relative flex-1 sm:w-64 sm:flex-none">
                    <label for="cari-pesanan" class="sr-only">Cari pesanan</label>
                    <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z"/></svg>
                    <input type="search" id="cari-pesanan" name="q" value="{{ request('q') }}" placeholder="No. pesanan atau nama" class="input py-2 pl-9">
                </div>
                <label for="sort_id" class="sr-only">Urutkan</label>
                <select name="sort_id" id="sort_id" onchange="this.form.submit()" class="input w-auto py-2">
                    <option value="desc" @selected(request('sort_id', 'desc') === 'desc')>Terbaru</option>
                    <option value="asc" @selected(request('sort_id') === 'asc')>Terlama</option>
                </select>
            </form>
        </x-page-header>

        {{-- Tab status --}}
        <nav class="-mx-4 mb-6 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter status">
            <ul class="flex w-max gap-2">
                <li>
                    <a href="{{ $tabUrl(null) }}" class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm transition-colors {{ $currentStatus === '' ? 'border-white bg-white text-zinc-950' : 'border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-white' }}">
                        Semua <span class="text-xs opacity-70">{{ $allCount }}</span>
                    </a>
                </li>
                @foreach ($statusLabels as $value => $label)
                    <li>
                        <a href="{{ $tabUrl($value) }}" class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm whitespace-nowrap transition-colors {{ $currentStatus === $value ? 'border-white bg-white text-zinc-950' : 'border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-white' }}">
                            {{ $label }} <span class="text-xs opacity-70">{{ $statusCounts[$value] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($orders->isEmpty())
            <x-empty-state title="Tidak ada pesanan" icon="receipt">
                {{ request('q') || $currentStatus ? 'Tidak ada pesanan yang cocok dengan filter ini.' : 'Pesanan dari pembeli akan muncul di sini.' }}
                @if (request('q') || $currentStatus)
                    <x-slot:action>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Hapus filter</a>
                    </x-slot:action>
                @endif
            </x-empty-state>
        @else
            <ul class="space-y-4">
                @foreach ($orders as $order)
                    @php
                        $code = '#TRX-'.str_pad($order->id_order, 4, '0', STR_PAD_LEFT);
                        $address = preg_match('/Nama:\s*(.*?)\s*No\. HP:\s*(.*?)\s*Alamat:\s*(.*)/is', $order->alamat_pengiriman, $m)
                            ? ['nama' => $m[1], 'hp' => $m[2], 'alamat' => $m[3]]
                            : ['nama' => '-', 'hp' => '-', 'alamat' => $order->alamat_pengiriman];
                    @endphp
                    <li class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-3">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                <span class="font-mono font-medium text-white">{{ $code }}</span>
                                <span class="text-zinc-500">{{ $order->tanggal_order?->translatedFormat('d M Y, H:i') }}</span>
                                <span class="text-zinc-500">· Akun: <span class="text-zinc-300">{{ $order->user->nama ?? 'Akun terhapus' }}</span></span>
                            </div>
                            <x-status-badge :order="$order" />
                        </div>

                        <div class="grid gap-5 px-5 py-4 md:grid-cols-[1.2fr_1fr_auto]">
                            <div>
                                <h3 class="text-xs font-medium text-zinc-500">Barang</h3>
                                <ul class="mt-2 space-y-1 text-sm">
                                    @foreach ($order->items as $item)
                                        <li class="text-zinc-300">
                                            {{ $item->variant?->product?->nama_product ?? 'Produk terhapus' }}
                                            <span class="text-zinc-500">· {{ $item->variant?->size?->nama_size ?? '-' }} × {{ $item->qty }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-xs font-medium text-zinc-500">Kirim ke</h3>
                                <p class="mt-2 text-sm text-zinc-200">{{ $address['nama'] }} · <a href="tel:{{ $address['hp'] }}" class="font-mono text-zinc-300 hover:text-white">{{ $address['hp'] }}</a></p>
                                <p class="mt-0.5 text-sm whitespace-pre-line text-zinc-400">{{ $address['alamat'] }}</p>
                                <p class="mt-2 text-xs text-zinc-500">{{ $order->kurir }}{{ $order->payment ? ' · '.$order->payment->metode_pembayaran : '' }}</p>
                            </div>

                            <div class="flex flex-col gap-3 md:items-end">
                                <p class="text-lg font-semibold whitespace-nowrap text-white">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>

                                <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id_order) }}"
                                      x-data="{ status: @js($order->status_pesanan), initial: @js($order->status_pesanan) }"
                                      :data-confirm="status === 'dibatalkan' ? 'Pesanan {{ $code }} akan ditandai dibatalkan.' : null"
                                      data-confirm-title="Batalkan pesanan?" data-confirm-button="Ya, batalkan"
                                      class="flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <label for="status-{{ $order->id_order }}" class="sr-only">Status {{ $code }}</label>
                                    <select id="status-{{ $order->id_order }}" name="status_pesanan" x-model="status" class="input w-auto py-2 text-sm">
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm" :disabled="status === initial">Simpan</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </main>
@endsection
