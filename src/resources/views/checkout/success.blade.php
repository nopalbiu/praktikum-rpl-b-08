@extends('layouts.app')

@section('title', 'Pesanan dibuat')

@php
    $total = (int) round($order->total_harga);
    $totalFormatted = number_format($total, 0, ',', '.');
    $orderCode = '#TRX-'.str_pad($order->id_order, 4, '0', STR_PAD_LEFT);
    preg_match('/\d{6,}/', $infoRekening, $m);
    $accountNumber = $m[0] ?? $infoRekening;

    $waNumber = '6285877539320';
    $waText = rawurlencode("Halo Admin WearWoreWorn!\n\nSaya mau konfirmasi pembayaran:\n- Pesanan: {$orderCode}\n- Total: Rp {$totalFormatted}\n\nBukti transfer saya lampirkan di pesan ini.");
    $waLink = "https://wa.me/{$waNumber}?text={$waText}";
    $isWaiting = $order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN;
@endphp

@section('content')
    <main class="container-page max-w-2xl pt-8 pb-12 sm:pt-12">
        <x-checkout-steps :current="3" />

        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-950/60 text-emerald-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-white sm:text-3xl">Pesanan dibuat</h1>
            <p class="mt-2 text-zinc-400">Selesaikan pembayaran supaya pesanan {{ $orderCode }} bisa kami proses.</p>
        </div>

        <section class="card mt-8 p-5 sm:p-6" x-data="{ copied: null, copy(text, key) { navigator.clipboard.writeText(text); this.copied = key; setTimeout(() => this.copied = null, 1500) } }">
            @if ($isWaiting)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-900/50 bg-amber-950/20 px-4 py-3"
                     x-data="countdown({{ $batasPembayaran->getTimestamp() * 1000 }})">
                    <span class="text-sm text-amber-200">Bayar sebelum</span>
                    <span class="font-mono text-sm font-semibold text-amber-300" x-text="label">{{ $batasPembayaran->translatedFormat('d M Y, H:i') }}</span>
                </div>
            @else
                <div class="flex items-center justify-between rounded-xl border border-zinc-800 px-4 py-3">
                    <span class="text-sm text-zinc-400">Status</span>
                    <span class="badge badge-neutral">{{ $order->statusLabel() }}</span>
                </div>
            @endif

            <dl class="mt-5 divide-y divide-zinc-800">
                <div class="flex items-center justify-between gap-4 py-4">
                    <div>
                        <dt class="text-xs text-zinc-500">Total transfer</dt>
                        <dd class="mt-1 text-2xl font-bold text-white">Rp {{ $totalFormatted }}</dd>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" @click="copy('{{ $total }}', 'total')">
                        <span x-text="copied === 'total' ? 'Disalin' : 'Salin'">Salin</span>
                    </button>
                </div>
                <div class="flex items-center justify-between gap-4 py-4">
                    <div class="min-w-0">
                        <dt class="text-xs text-zinc-500">Rekening tujuan</dt>
                        <dd class="mt-1 font-mono text-base break-words text-white">{{ $infoRekening }}</dd>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm shrink-0" @click="copy('{{ $accountNumber }}', 'rek')">
                        <span x-text="copied === 'rek' ? 'Disalin' : 'Salin'">Salin</span>
                    </button>
                </div>
            </dl>

            <ol class="mt-2 space-y-3 rounded-xl bg-zinc-900/60 p-4 text-sm text-zinc-400">
                <li class="flex gap-3"><span class="font-semibold text-white">1</span> Transfer tepat Rp {{ $totalFormatted }} ke rekening di atas.</li>
                <li class="flex gap-3"><span class="font-semibold text-white">2</span> Simpan atau screenshot bukti transfer.</li>
                <li class="flex gap-3"><span class="font-semibold text-white">3</span> Kirim bukti lewat WhatsApp dengan tombol di bawah.</li>
            </ol>

            <div class="mt-6 grid gap-2 sm:grid-cols-2">
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-primary btn-lg">Konfirmasi via WhatsApp</a>
                <a href="{{ route('user.profile') }}#pesanan" class="btn btn-secondary btn-lg">Lihat pesanan saya</a>
            </div>
        </section>

        <section class="card mt-6 p-5 sm:p-6">
            <h2 class="text-base font-semibold text-white">Detail pesanan</h2>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4">
                        <span class="text-zinc-300">{{ $item->variant->product->nama_product ?? 'Produk' }} <span class="text-zinc-500">× {{ $item->qty }}</span></span>
                        <span class="whitespace-nowrap text-zinc-200">Rp {{ number_format($item->harga_satuan * $item->qty, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 border-t border-zinc-800 pt-4 text-sm text-zinc-500">Dikirim dengan {{ $order->kurir }} ke:</p>
            <p class="mt-1 text-sm whitespace-pre-line text-zinc-300">{{ $order->alamat_pengiriman }}</p>
        </section>

        <p class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-sm text-zinc-400 hover:text-white">Lanjut belanja</a>
        </p>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('countdown', (deadline) => ({
                label: '',
                init() {
                    const tick = () => {
                        const left = deadline - Date.now();
                        if (left <= 0) {
                            this.label = 'Waktu habis';
                            return clearInterval(this.timer);
                        }
                        const h = Math.floor(left / 3.6e6);
                        const m = Math.floor((left % 3.6e6) / 6e4);
                        const s = Math.floor((left % 6e4) / 1000);
                        this.label = `${h} jam ${String(m).padStart(2, '0')} mnt ${String(s).padStart(2, '0')} dtk`;
                    };
                    tick();
                    this.timer = setInterval(tick, 1000);
                },
                destroy() { clearInterval(this.timer); },
            }));
        });
    </script>
@endpush
