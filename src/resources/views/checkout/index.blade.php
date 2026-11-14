@extends('layouts.app')

@section('title', 'Checkout')

@php
    use App\Support\ImageUrl;

    $courierData = collect($courierOptions)->map(fn ($c) => $c['ongkir'] + $c['asuransi']);
    $bankLabels = [
        'Transfer Bank BCA' => 'BCA',
        'Transfer Bank Mandiri' => 'Mandiri',
        'Transfer Bank BRI' => 'BRI',
    ];
@endphp

@section('content')
    <main class="container-page pt-8 pb-12 sm:pt-12">
        <x-checkout-steps :current="2" />
        <x-page-header title="Checkout" subtitle="Lengkapi alamat pengiriman lalu pilih kurir dan metode pembayaran." />

        @if ($hasOutOfStock)
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-900/60 bg-amber-950/30 p-4 text-sm text-amber-200" role="alert">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.3 3.1c.8-1.4 2.7-1.4 3.4 0l6 10.6c.8 1.4-.2 3.1-1.7 3.1H4c-1.6 0-2.5-1.7-1.7-3.1l6-10.6zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                <p>Ada barang yang stoknya tidak cukup. <a href="{{ route('cart.index') }}" class="underline">Kembali ke keranjang</a> untuk menyesuaikan jumlahnya.</p>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST"
              x-data="{ courier: @js(old('kurir')), fees: @js($courierData), subtotal: {{ (float) $totalItemPrice }},
                        get shipping() { return this.courier ? this.fees[this.courier] : 0 },
                        rupiah(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(v)) } }"
              class="grid gap-8 lg:grid-cols-[1fr_24rem] lg:items-start">
            @csrf
            @foreach ($selectedItems as $itemId)
                <input type="hidden" name="items[]" value="{{ $itemId }}">
            @endforeach

            <div class="flex flex-col gap-6">
                {{-- Alamat --}}
                <section class="card p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-white">Alamat pengiriman</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nama_penerima" class="label">Nama penerima</label>
                            <input type="text" id="nama_penerima" name="nama_penerima" value="{{ old('nama_penerima', $user->nama) }}" required autocomplete="name"
                                   class="input @error('nama_penerima') input-error @enderror">
                            @error('nama_penerima') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="no_hp" class="label">Nomor HP</label>
                            <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required inputmode="numeric" autocomplete="tel"
                                   placeholder="081234567890" pattern="[0-9]{11,20}" title="Minimal 11 digit angka"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="input @error('no_hp') input-error @enderror">
                            @error('no_hp') <p class="field-error">{{ $message }}</p> @else <p class="mt-1.5 text-xs text-zinc-500">Dipakai kurir untuk menghubungi kamu.</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="alamat" class="label">Alamat lengkap</label>
                            <textarea id="alamat" name="alamat" rows="3" required autocomplete="street-address"
                                      placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"
                                      class="input resize-none @error('alamat') input-error @enderror">{{ old('alamat') }}</textarea>
                            @error('alamat') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Kurir --}}
                <section class="card p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-white">Kurir</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ($courierOptions as $name => $data)
                            <label class="cursor-pointer">
                                <input type="radio" name="kurir" value="{{ $name }}" x-model="courier" required class="peer sr-only">
                                <span class="flex h-full flex-col rounded-xl border border-zinc-800 p-4 transition-colors hover:border-zinc-600 peer-checked:border-white peer-checked:bg-zinc-900 peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500">
                                    <span class="text-sm font-semibold text-white">{{ $name }}</span>
                                    <span class="mt-1 text-sm text-zinc-400">Rp {{ number_format($data['ongkir'], 0, ',', '.') }}</span>
                                    <span class="mt-0.5 text-xs text-zinc-500">+ asuransi Rp {{ number_format($data['asuransi'], 0, ',', '.') }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('kurir') <p class="field-error">{{ $message }}</p> @enderror
                </section>

                {{-- Pembayaran --}}
                <section class="card p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-white">Metode pembayaran</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ($bankAccounts as $method)
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="{{ $method }}" @checked(old('metode_pembayaran') === $method) required class="peer sr-only">
                                <span class="flex h-full flex-col rounded-xl border border-zinc-800 p-4 transition-colors hover:border-zinc-600 peer-checked:border-white peer-checked:bg-zinc-900 peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500">
                                    <span class="text-sm font-semibold text-white">{{ $bankLabels[$method] ?? $method }}</span>
                                    <span class="mt-1 text-xs text-zinc-500">Transfer bank</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('metode_pembayaran') <p class="field-error">{{ $message }}</p> @enderror
                    <p class="mt-3 text-xs text-zinc-500">Nomor rekening tujuan muncul setelah pesanan dibuat. Batas pembayaran 24 jam.</p>
                </section>
            </div>

            {{-- Ringkasan --}}
            <aside class="card p-5 sm:p-6 lg:sticky lg:top-24">
                <h2 class="text-base font-semibold text-white">Ringkasan pesanan</h2>

                <ul class="mt-4 max-h-72 space-y-4 overflow-y-auto pr-1">
                    @foreach ($cart->items as $item)
                        @php $image = $item->variant->product->images->firstWhere('is_primary', 1) ?? $item->variant->product->images->first(); @endphp
                        <li class="flex gap-3">
                            <img src="{{ ImageUrl::make($image?->url_gambar, 120) }}" alt="" loading="lazy"
                                 onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                                 class="h-16 w-14 shrink-0 rounded-md bg-zinc-900 object-cover">
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="line-clamp-1 font-medium text-zinc-200">{{ $item->variant->product->nama_product }}</p>
                                <p class="text-xs text-zinc-500">Ukuran {{ $item->variant->size->nama_size }} × {{ $item->qty }}</p>
                            </div>
                            <p class="text-sm whitespace-nowrap text-zinc-200">Rp {{ number_format($item->qty * $item->variant->product->harga, 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-5 space-y-2 border-t border-zinc-800 pt-4 text-sm">
                    <div class="flex justify-between text-zinc-400">
                        <dt>Subtotal</dt>
                        <dd class="text-zinc-200">Rp {{ number_format($totalItemPrice, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between text-zinc-400">
                        <dt>Ongkir + asuransi</dt>
                        <dd class="text-zinc-200" x-text="courier ? rupiah(shipping) : 'Pilih kurir'">Pilih kurir</dd>
                    </div>
                    <div class="flex justify-between border-t border-zinc-800 pt-3 text-base">
                        <dt class="font-medium text-white">Total</dt>
                        <dd class="font-semibold text-white" x-text="rupiah(subtotal + shipping)">Rp {{ number_format($totalItemPrice, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <button type="submit" class="btn btn-primary btn-lg mt-6 w-full" @disabled($hasOutOfStock)>Buat pesanan</button>
                <a href="{{ route('cart.index') }}" class="btn btn-ghost mt-2 w-full">Kembali ke keranjang</a>
            </aside>
        </form>
    </main>
@endsection
