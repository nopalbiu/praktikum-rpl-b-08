@extends('layouts.app')

@section('title', $product->nama_product)
@section('description', \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 150))

@php
    use App\Support\ImageUrl;

    $gallery = $product->images->map(fn ($img) => [
        'full' => ImageUrl::make($img->url_gambar, 1200),
        'thumb' => ImageUrl::make($img->url_gambar, 200),
        'label' => $product->nama_product,
    ])->values();

    if ($product->url_size_chart) {
        $gallery->push([
            'full' => ImageUrl::make($product->url_size_chart, 1200),
            'thumb' => ImageUrl::make($product->url_size_chart, 200),
            'label' => 'Panduan ukuran',
            'isSizeChart' => true,
        ]);
    }
    if ($gallery->isEmpty()) {
        $gallery->push(['full' => ImageUrl::placeholder(), 'thumb' => ImageUrl::placeholder(), 'label' => $product->nama_product]);
    }

    $stockBySize = $variants->mapWithKeys(fn ($v) => [$v->nama_size => (int) $v->stok]);
    $onlyOneSize = $variants->count() === 1;
    $initialSize = $onlyOneSize ? $variants->first()->nama_size : old('size');
    $sizeChartIndex = $gallery->search(fn ($g) => ! empty($g['isSizeChart']));
    $price = 'Rp '.number_format($product->harga, 0, ',', '.');
@endphp

@section('content')
    <main class="container-page pt-6 pb-28 sm:pt-10 lg:pb-10"
          x-data="productPage({ stock: @js($stockBySize), total: {{ (int) $totalStock }}, size: @js($initialSize), images: {{ $gallery->count() }} })">

        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-zinc-500">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Katalog</a></li>
                <li aria-hidden="true">/</li>
                <li class="truncate text-zinc-300" aria-current="page">{{ $product->nama_product }}</li>
            </ol>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-14">
            {{-- Galeri --}}
            <div class="flex flex-col gap-3 lg:sticky lg:top-24 lg:self-start">
                <div class="relative aspect-square overflow-hidden rounded-2xl bg-zinc-900">
                    @foreach ($gallery as $i => $img)
                        <img src="{{ $img['full'] }}" alt="{{ $img['label'] }}"
                             x-show="active === {{ $i }}" @if ($i > 0) x-cloak loading="lazy" @endif
                             onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                             class="h-full w-full {{ empty($img['isSizeChart']) ? 'object-cover' : 'object-contain bg-white' }}">
                    @endforeach

                    @if ($gallery->count() > 1)
                        <button type="button" @click="prev()" class="absolute top-1/2 left-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-zinc-950/70 text-white backdrop-blur hover:bg-zinc-950" aria-label="Gambar sebelumnya">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 01-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        </button>
                        <button type="button" @click="next()" class="absolute top-1/2 right-3 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-zinc-950/70 text-white backdrop-blur hover:bg-zinc-950" aria-label="Gambar berikutnya">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.3 14.7a1 1 0 010-1.4L10.6 10 7.3 6.7a1 1 0 011.4-1.4l4 4a1 1 0 010 1.4l-4 4a1 1 0 01-1.4 0z" clip-rule="evenodd"/></svg>
                        </button>
                        <span class="absolute right-3 bottom-3 rounded-full bg-zinc-950/70 px-2.5 py-1 text-xs text-zinc-300 backdrop-blur" x-text="`${active + 1} / {{ $gallery->count() }}`"></span>
                    @endif
                </div>

                @if ($gallery->count() > 1)
                    <div class="grid grid-cols-5 gap-2 sm:gap-3">
                        @foreach ($gallery as $i => $img)
                            <button type="button" @click="active = {{ $i }}"
                                    :class="active === {{ $i }} ? 'ring-2 ring-white' : 'opacity-60 hover:opacity-100'"
                                    class="relative aspect-square overflow-hidden rounded-lg bg-zinc-900 transition"
                                    aria-label="Lihat {{ $img['label'] }}">
                                <img src="{{ $img['thumb'] }}" alt="" loading="lazy" class="h-full w-full object-cover"
                                     onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'">
                                @if (! empty($img['isSizeChart']))
                                    <span class="absolute inset-0 flex items-center justify-center bg-zinc-950/60 text-[10px] font-semibold tracking-wider text-white uppercase">Ukuran</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info & pembelian --}}
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-4xl">{{ $product->nama_product }}</h1>
                <p class="mt-3 text-2xl font-semibold text-white">{{ $price }}</p>

                <form id="cart-form" action="{{ route('cart.add', $product->id_product) }}" method="POST" class="mt-8" @submit="validate($event)">
                    @csrf

                    {{-- Ukuran --}}
                    <fieldset>
                        <div class="mb-3 flex items-center justify-between">
                            <legend class="text-sm font-medium text-zinc-300">
                                Ukuran
                                <span class="text-zinc-500" x-show="size" x-text="`: ${size}`"></span>
                            </legend>
                            @if ($sizeChartIndex !== false)
                                <button type="button" @click="active = {{ $sizeChartIndex }}; $el.closest('main').scrollIntoView({ behavior: 'smooth' })" class="text-sm text-blue-400 hover:text-blue-300">Panduan ukuran</button>
                            @endif
                        </div>

                        @if ($variants->isEmpty())
                            <p class="rounded-lg border border-zinc-800 bg-zinc-900 px-4 py-3 text-sm text-zinc-400">Ukuran untuk produk ini belum tersedia.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($variants as $variant)
                                    @php $habis = (int) $variant->stok <= 0; @endphp
                                    <label class="{{ $habis ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                        <input type="radio" name="size" value="{{ $variant->nama_size }}" x-model="size"
                                               @disabled($habis) class="peer sr-only">
                                        <span class="inline-flex h-11 min-w-14 items-center justify-center rounded-lg border px-4 text-sm font-medium transition-colors
                                                     peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-500
                                                     {{ $habis
                                                        ? 'border-zinc-800 text-zinc-600 line-through'
                                                        : 'border-zinc-700 text-zinc-200 hover:border-zinc-500 peer-checked:border-white peer-checked:bg-white peer-checked:text-zinc-950' }}"
                                              @if ($habis) title="Stok habis" @endif>
                                            {{ $variant->nama_size }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <p x-cloak x-show="sizeError" class="field-error" role="alert">Pilih ukuran dulu.</p>
                        @error('size') <p class="field-error">{{ $message }}</p> @enderror
                    </fieldset>

                    {{-- Jumlah --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <div class="flex h-11 items-center rounded-lg border border-zinc-700">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1" class="flex h-full w-11 items-center justify-center text-zinc-400 hover:text-white disabled:opacity-40" aria-label="Kurangi jumlah">−</button>
                            <input type="number" name="quantity" x-model.number="qty" min="1" :max="maxQty" readonly
                                   class="h-full w-12 [appearance:textfield] bg-transparent text-center text-sm font-semibold text-white focus:outline-none [&::-webkit-inner-spin-button]:appearance-none" aria-label="Jumlah">
                            <button type="button" @click="qty = Math.min(maxQty, qty + 1)" :disabled="qty >= maxQty" class="flex h-full w-11 items-center justify-center text-zinc-400 hover:text-white disabled:opacity-40" aria-label="Tambah jumlah">+</button>
                        </div>
                        <p class="text-sm" :class="available > 0 && available <= 5 ? 'text-amber-400' : 'text-zinc-500'">
                            <template x-if="available <= 0"><span>Stok habis</span></template>
                            <template x-if="available > 0 && available <= 5"><span x-text="`Sisa ${available} lagi`"></span></template>
                            <template x-if="available > 5"><span x-text="`Stok: ${available}`"></span></template>
                        </p>
                    </div>

                    {{-- Tombol (desktop) --}}
                    <div class="mt-8 hidden gap-3 sm:grid sm:grid-cols-2">
                        @auth
                            <button type="submit" class="btn btn-primary btn-lg" :disabled="available <= 0">
                                <span x-text="available <= 0 ? 'Stok habis' : 'Tambah ke keranjang'">Tambah ke keranjang</span>
                            </button>
                            <button type="submit" formaction="{{ route('cart.buyNow', $product->id_product) }}" class="btn btn-secondary btn-lg" :disabled="available <= 0">Beli sekarang</button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Masuk untuk membeli</a>
                            <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Buat akun</a>
                        @endauth
                    </div>
                </form>

                {{-- Detail --}}
                <div class="mt-10 divide-y divide-zinc-800 border-y border-zinc-800">
                    <details class="group py-4" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-white">
                            Deskripsi
                            <svg class="h-4 w-4 text-zinc-500 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed whitespace-pre-line text-zinc-400">{{ $product->deskripsi ?: 'Belum ada deskripsi.' }}</p>
                    </details>

                    @if ($product->material_pakaian)
                        <details class="group py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-white">
                                Bahan
                                <svg class="h-4 w-4 text-zinc-500 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                            </summary>
                            <p class="mt-3 text-sm text-zinc-400">{{ $product->material_pakaian }}</p>
                        </details>
                    @endif

                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-white">
                            Pengiriman dan pembayaran
                            <svg class="h-4 w-4 text-zinc-500 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                        </summary>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-zinc-400">
                            <li>Dikirim lewat JNE, J&amp;T, atau SiCepat.</li>
                            <li>Pembayaran lewat transfer bank (BCA, Mandiri, BRI).</li>
                            <li>Pesanan dibatalkan otomatis jika belum dibayar dalam 24 jam.</li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        {{-- Bar beli yang menempel di bawah (mobile) --}}
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-800 bg-zinc-950/95 px-4 py-3 backdrop-blur sm:hidden">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs text-zinc-500" x-text="size ? `Ukuran ${size} · ${qty} pcs` : 'Pilih ukuran'">Pilih ukuran</p>
                    <p class="text-base font-semibold text-white">{{ $price }}</p>
                </div>
                @auth
                    <button type="submit" form="cart-form" formaction="{{ route('cart.buyNow', $product->id_product) }}" class="btn btn-secondary" :disabled="available <= 0" aria-label="Beli sekarang">Beli</button>
                    <button type="submit" form="cart-form" class="btn btn-primary" :disabled="available <= 0">
                        <span x-text="available <= 0 ? 'Habis' : '+ Keranjang'">+ Keranjang</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk untuk membeli</a>
                @endauth
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productPage', ({ stock, total, size, images }) => ({
                stock,
                size,
                qty: 1,
                active: 0,
                sizeError: false,

                get available() {
                    return this.size ? (this.stock[this.size] ?? 0) : total;
                },
                get maxQty() {
                    return Math.max(1, Math.min(99, this.available));
                },
                init() {
                    this.$watch('size', () => {
                        this.sizeError = false;
                        this.qty = Math.min(this.qty, this.maxQty);
                    });
                },
                next() { this.active = (this.active + 1) % images; },
                prev() { this.active = (this.active - 1 + images) % images; },
                validate(event) {
                    if (!this.size) {
                        event.preventDefault();
                        this.sizeError = true;
                        document.getElementById('cart-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                },
            }));
        });
    </script>
@endpush
