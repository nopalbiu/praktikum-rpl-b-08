@extends('layouts.app')

@section('title', 'Keranjang')

@php
    use App\Support\ImageUrl;

    $items = $cart?->items ?? collect();
    $itemsData = $items->map(fn ($item) => [
        'id' => $item->id_cart_item,
        'price' => (float) $item->variant->product->harga,
        'qty' => (int) $item->qty,
        'stock' => (int) $item->variant->stok,
        'selected' => $item->variant->stok >= $item->qty && $item->variant->stok > 0,
    ])->values();
@endphp

@section('content')
    <main class="container-page pt-8 pb-28 sm:pt-12 lg:pb-10">
        <x-page-header title="Keranjang" :subtitle="$items->isNotEmpty() ? 'Pilih barang yang mau di-checkout.' : null" />

        @if ($items->isEmpty())
            <x-empty-state title="Keranjangmu masih kosong" icon="bag">
                Lihat koleksi terbaru dan tambahkan barang yang kamu suka.
                <x-slot:action>
                    <a href="{{ route('home') }}" class="btn btn-primary">Mulai belanja</a>
                </x-slot:action>
            </x-empty-state>
        @else
            <form action="{{ route('checkout.index') }}" method="GET" id="checkout-form"
                  x-data="cartPage(@js($itemsData))" @submit="if (!selectedCount) $event.preventDefault()"
                  class="grid gap-8 lg:grid-cols-[1fr_22rem] lg:items-start">

                {{-- Daftar barang --}}
                <section aria-label="Barang di keranjang">
                    <label class="mb-3 flex w-fit cursor-pointer items-center gap-3 px-1 text-sm text-zinc-400">
                        <input type="checkbox" class="h-4 w-4 rounded border-zinc-700 bg-zinc-900 accent-blue-500"
                               :checked="allSelected" @change="toggleAll($event.target.checked)">
                        <span>Pilih semua (<span x-text="selectableCount"></span>)</span>
                    </label>

                    <ul class="divide-y divide-zinc-800 rounded-2xl border border-zinc-800">
                        @foreach ($items as $i => $item)
                            @php
                                $product = $item->variant->product;
                                $stock = (int) $item->variant->stok;
                                $image = $product->images->firstWhere('is_primary', 1) ?? $product->images->first();
                            @endphp
                            <li class="flex gap-3 p-4 sm:gap-4 sm:p-5" :class="items[{{ $i }}].stock < items[{{ $i }}].qty && 'bg-red-950/10'">
                                <div class="pt-1">
                                    <input type="checkbox" name="items[]" value="{{ $item->id_cart_item }}"
                                           x-model="items[{{ $i }}].selected"
                                           :disabled="items[{{ $i }}].stock < items[{{ $i }}].qty || items[{{ $i }}].stock <= 0"
                                           class="h-4 w-4 rounded border-zinc-700 bg-zinc-900 accent-blue-500 disabled:opacity-30"
                                           aria-label="Pilih {{ $product->nama_product }}">
                                </div>

                                <a href="{{ route('product.show', $product->nama_product) }}" class="shrink-0">
                                    <img src="{{ ImageUrl::make($image?->url_gambar, 200) }}" alt="" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                                         class="h-24 w-20 rounded-lg bg-zinc-900 object-cover sm:h-28 sm:w-24 {{ $stock <= 0 ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ route('product.show', $product->nama_product) }}" class="line-clamp-2 text-sm font-medium text-white hover:underline sm:text-base">{{ $product->nama_product }}</a>
                                            <p class="mt-0.5 text-sm text-zinc-500">Ukuran {{ $item->variant->size->nama_size }} · Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                                        </div>
                                        <button type="submit" form="hapus-{{ $item->id_cart_item }}" class="-mt-1 -mr-1 shrink-0 rounded-lg p-2 text-zinc-500 hover:bg-zinc-800 hover:text-red-300" aria-label="Hapus {{ $product->nama_product }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </div>

                                    <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-3">
                                        @if ($stock <= 0)
                                            <span class="badge badge-danger">Stok habis</span>
                                        @else
                                            <div>
                                                <div class="flex h-9 items-center rounded-lg border border-zinc-700">
                                                    <button type="button" @click="changeQty({{ $i }}, -1)" :disabled="items[{{ $i }}].qty <= 1 || busy === {{ $i }}"
                                                            class="flex h-full w-9 items-center justify-center text-zinc-400 hover:text-white disabled:opacity-30" aria-label="Kurangi">−</button>
                                                    <span class="w-8 text-center text-sm font-semibold text-white" x-text="items[{{ $i }}].qty">{{ $item->qty }}</span>
                                                    <button type="button" @click="changeQty({{ $i }}, 1)" :disabled="items[{{ $i }}].qty >= Math.min(99, items[{{ $i }}].stock) || busy === {{ $i }}"
                                                            class="flex h-full w-9 items-center justify-center text-zinc-400 hover:text-white disabled:opacity-30" aria-label="Tambah">+</button>
                                                </div>
                                                <p x-cloak x-show="items[{{ $i }}].stock < items[{{ $i }}].qty" class="mt-1 text-xs text-red-400">Stok tinggal {{ $stock }}, kurangi jumlahnya.</p>
                                                <p x-show="items[{{ $i }}].stock >= items[{{ $i }}].qty && items[{{ $i }}].stock <= 5" class="mt-1 text-xs text-amber-400">Sisa {{ $stock }}</p>
                                            </div>
                                        @endif
                                        <p class="text-sm font-semibold text-white sm:text-base" x-text="rupiah(items[{{ $i }}].price * items[{{ $i }}].qty)">
                                            Rp {{ number_format($product->harga * $item->qty, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Ringkasan (desktop) --}}
                <aside class="card hidden p-6 lg:sticky lg:top-24 lg:block">
                    <h2 class="text-base font-semibold text-white">Ringkasan</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-zinc-400">
                            <dt>Barang dipilih</dt>
                            <dd x-text="`${selectedQty} pcs`"></dd>
                        </div>
                        <div class="flex justify-between border-t border-zinc-800 pt-3 text-base">
                            <dt class="text-zinc-300">Subtotal</dt>
                            <dd class="font-semibold text-white" x-text="rupiah(total)"></dd>
                        </div>
                    </dl>
                    <p class="mt-2 text-xs text-zinc-500">Ongkos kirim dihitung di halaman checkout.</p>
                    <button type="submit" class="btn btn-primary btn-lg mt-6 w-full" :disabled="!selectedCount">
                        Checkout <span x-show="selectedCount" x-text="`(${selectedCount})`"></span>
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-ghost mt-2 w-full">Lanjut belanja</a>
                </aside>

                {{-- Bar checkout (mobile/tablet) --}}
                <div class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-800 bg-zinc-950/95 px-4 py-3 backdrop-blur lg:hidden">
                    <div class="container-page flex items-center gap-4 px-0">
                        <div class="flex-1">
                            <p class="text-xs text-zinc-500" x-text="`${selectedQty} pcs dipilih`"></p>
                            <p class="text-base font-semibold text-white" x-text="rupiah(total)"></p>
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="!selectedCount">Checkout</button>
                    </div>
                </div>
            </form>

            @foreach ($items as $item)
                <form id="hapus-{{ $item->id_cart_item }}" action="{{ route('cart.remove', $item->id_cart_item) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cartPage', (items) => ({
                items,
                busy: null,

                get selectable() { return this.items.filter((i) => i.stock > 0 && i.stock >= i.qty); },
                get selectableCount() { return this.selectable.length; },
                get selected() { return this.items.filter((i) => i.selected && i.stock >= i.qty && i.stock > 0); },
                get selectedCount() { return this.selected.length; },
                get selectedQty() { return this.selected.reduce((sum, i) => sum + i.qty, 0); },
                get total() { return this.selected.reduce((sum, i) => sum + i.price * i.qty, 0); },
                get allSelected() { return this.selectableCount > 0 && this.selectedCount === this.selectableCount; },

                toggleAll(checked) {
                    this.selectable.forEach((i) => (i.selected = checked));
                },
                rupiah(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
                },
                async changeQty(index, delta) {
                    const item = this.items[index];
                    const qty = Math.max(1, Math.min(99, item.stock, item.qty + delta));
                    if (qty === item.qty) return;

                    const previous = item.qty;
                    item.qty = qty;
                    this.busy = index;
                    try {
                        const response = await fetch(`{{ url('/cart/update') }}/${item.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ _method: 'PATCH', qty }),
                        });
                        if (!response.ok || response.redirected) throw new Error();
                        if (item.stock >= item.qty && item.stock > 0) item.selected = item.selected || previous > item.stock;
                    } catch {
                        item.qty = previous;
                    } finally {
                        this.busy = null;
                    }
                },
            }));
        });
    </script>
@endpush
