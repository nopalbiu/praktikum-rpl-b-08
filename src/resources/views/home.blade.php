@extends('layouts.app')

@section('title', 'Katalog')

@php
    $priceRanges = [
        '0-200000' => 'Di bawah Rp200.000',
        '200000-300000' => 'Rp200.000 – Rp300.000',
        '300000-9999999' => 'Di atas Rp300.000',
    ];
    $sortOptions = [
        'terbaru' => 'Terbaru',
        'termurah' => 'Harga terendah',
        'termahal' => 'Harga tertinggi',
    ];

    $selectedCategories = array_map('strval', (array) request('category', []));
    $selectedSizes = array_map('strval', (array) request('size', []));
    $selectedPrices = (array) request('price', []);

    // Chip filter aktif (diperbarui bersama hasil AJAX)
    $chips = [];
    if (filled(request('search'))) {
        $chips[] = ['label' => '“'.request('search').'”', 'target' => 'search-input'];
    }
    foreach ($categories as $c) {
        if (in_array((string) $c->id_category, $selectedCategories, true)) {
            $chips[] = ['label' => $c->nama_category, 'target' => 'category-'.$c->id_category];
        }
    }
    foreach ($sizes as $s) {
        if (in_array((string) $s->id_size, $selectedSizes, true)) {
            $chips[] = ['label' => 'Ukuran '.$s->nama_size, 'target' => 'size-'.$s->id_size];
        }
    }
    foreach ($priceRanges as $value => $label) {
        if (in_array($value, $selectedPrices, true)) {
            $chips[] = ['label' => $label, 'target' => 'price-'.$value];
        }
    }
    $filterCount = count($chips) - (filled(request('search')) ? 1 : 0);
@endphp

@section('content')
    <main class="container-page pt-8 pb-4 sm:pt-12" x-data="{ filters: false }" @keydown.escape.window="filters = false">

        {{-- Hero --}}
        <section class="mb-8 overflow-hidden rounded-2xl border border-zinc-800/80 bg-gradient-to-br from-zinc-900 to-zinc-950 px-6 py-8 sm:px-10 sm:py-10">
            <p class="text-xs font-semibold tracking-[0.2em] text-blue-400 uppercase">WearWoreWorn</p>
            <h1 class="mt-2 max-w-2xl text-2xl leading-tight font-bold tracking-tight text-white sm:text-4xl">Streetwear untuk dipakai setiap hari</h1>
            <p class="mt-3 max-w-xl text-sm text-zinc-400 sm:text-base">Koleksi kaos, kemeja, hoodie, dan aksesori yang nyaman dipakai dan gampang dipadukan.</p>
        </section>

        <div class="flex flex-col gap-8 lg:flex-row lg:gap-10">

            {{-- Latar gelap saat panel filter terbuka (mobile) --}}
            <div x-cloak x-show="filters" x-transition.opacity @click="filters = false"
                 class="fixed inset-0 z-[55] bg-black/60 lg:hidden"></div>

            {{-- Panel filter: sidebar di desktop, laci dari kiri di mobile --}}
            <aside :class="filters ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-[56] w-[85%] max-w-sm -translate-x-full overflow-y-auto border-r border-zinc-800 bg-zinc-950 p-5 transition-transform duration-200 lg:static lg:z-auto lg:w-64 lg:max-w-none lg:shrink-0 lg:translate-x-0 lg:overflow-visible lg:border-0 lg:bg-transparent lg:p-0"
                   aria-label="Filter produk">
                <form id="filter-form" action="{{ route('home') }}" method="GET" class="flex flex-col gap-7 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between lg:hidden">
                        <h2 class="text-lg font-semibold text-white">Filter</h2>
                        <button type="button" @click="filters = false" class="btn btn-ghost -mr-2 h-10 w-10 p-0" aria-label="Tutup filter">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </div>

                    <div>
                        <label for="search-input" class="sr-only">Cari produk</label>
                        <div class="relative" x-data="{ q: @js((string) request('search', '')) }">
                            <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z"/></svg>
                            <input type="text" name="search" id="search-input" x-model="q" placeholder="Cari produk" autocomplete="off" enterkeyhint="search" class="input pr-9 pl-9">
                            <button type="button" x-cloak x-show="q" data-clear-search
                                    class="absolute top-1/2 right-1.5 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-800 hover:text-white"
                                    aria-label="Hapus kata kunci">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 00-1.4 1.4L8.6 10l-3.7 3.3a1 1 0 101.4 1.4L10 11.4l3.3 3.3a1 1 0 001.4-1.4L11.4 10l3.3-3.3a1 1 0 00-1.4-1.4L10 8.6 6.3 5.3z"/></svg>
                            </button>
                        </div>
                    </div>

                    <fieldset>
                        <legend class="mb-3 text-xs font-semibold tracking-wider text-zinc-500 uppercase">Kategori</legend>
                        <div class="flex flex-col gap-1">
                            @foreach ($categories as $category)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm text-zinc-300 hover:bg-zinc-900">
                                    <input type="checkbox" name="category[]" id="category-{{ $category->id_category }}" value="{{ $category->id_category }}"
                                           @checked(in_array((string) $category->id_category, $selectedCategories, true))
                                           class="filter-checkbox h-4 w-4 rounded border-zinc-700 bg-zinc-900 accent-blue-500">
                                    <span class="flex-1">{{ $category->nama_category }}</span>
                                    <span class="text-xs {{ $category->products_count ? 'text-zinc-500' : 'text-zinc-700' }}">{{ $category->products_count }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-3 text-xs font-semibold tracking-wider text-zinc-500 uppercase">Ukuran</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($sizes as $size)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="size[]" id="size-{{ $size->id_size }}" value="{{ $size->id_size }}"
                                           @checked(in_array((string) $size->id_size, $selectedSizes, true))
                                           class="filter-checkbox peer sr-only">
                                    <span class="inline-flex h-9 min-w-11 items-center justify-center rounded-lg border border-zinc-800 px-3 text-xs font-medium text-zinc-400 transition-colors peer-checked:border-white peer-checked:bg-white peer-checked:text-zinc-950 peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500 hover:border-zinc-600">
                                        {{ $size->nama_size }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-3 text-xs font-semibold tracking-wider text-zinc-500 uppercase">Harga</legend>
                        <div class="flex flex-col gap-1">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm text-zinc-300 hover:bg-zinc-900">
                                <input type="radio" name="price[]" id="price-all" value=""
                                       @checked(empty(array_filter($selectedPrices)))
                                       class="filter-checkbox h-4 w-4 border-zinc-700 bg-zinc-900 accent-blue-500">
                                Semua harga
                            </label>
                            @foreach ($priceRanges as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm text-zinc-300 hover:bg-zinc-900">
                                    <input type="radio" name="price[]" id="price-{{ $value }}" value="{{ $value }}"
                                           @checked(in_array($value, $selectedPrices, true))
                                           class="filter-checkbox h-4 w-4 border-zinc-700 bg-zinc-900 accent-blue-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="sticky bottom-0 -mx-5 mt-auto grid grid-cols-2 gap-2 border-t border-zinc-800 bg-zinc-950 p-5 lg:hidden">
                        <button type="button" class="btn btn-secondary" data-clear-filters>Reset</button>
                        <button type="button" class="btn btn-primary" @click="filters = false">Lihat hasil</button>
                    </div>
                </form>
            </aside>

            {{-- Daftar produk --}}
            <section class="min-w-0 flex-1" aria-labelledby="judul-produk">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h2 id="judul-produk" class="text-base font-semibold whitespace-nowrap text-white sm:text-lg">Semua produk</h2>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="filters = true" class="btn btn-secondary btn-sm lg:hidden">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M3 6h18M6 12h12M10 18h4"/></svg>
                            Filter
                            <span id="filter-count" class="{{ $filterCount ? '' : 'hidden' }} rounded-full bg-blue-600 px-1.5 text-[11px] text-white">{{ $filterCount }}</span>
                        </button>

                        <label for="sort-select" class="sr-only">Urutkan</label>
                        <select name="sort" id="sort-select" form="filter-form" class="input w-auto py-1.5 pr-8 text-sm">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('sort', 'terbaru') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="ajax-product-area" class="transition-opacity duration-200" aria-live="polite">
                    {{-- Ringkasan + chip filter aktif --}}
                    <div class="mb-6 flex flex-wrap items-center gap-2 text-sm" data-filter-count="{{ $filterCount }}">
                        <span class="mr-1 text-zinc-500">{{ $products->total() }} produk</span>
                        @foreach ($chips as $chip)
                            <button type="button" data-uncheck="{{ $chip['target'] }}"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-zinc-800 bg-zinc-900 py-1 pr-2 pl-3 text-xs text-zinc-300 transition-colors hover:border-zinc-600 hover:text-white">
                                {{ $chip['label'] }}
                                <svg class="h-3.5 w-3.5 text-zinc-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 00-1.4 1.4L8.6 10l-3.7 3.3a1 1 0 101.4 1.4L10 11.4l3.3 3.3a1 1 0 001.4-1.4L11.4 10l3.3-3.3a1 1 0 00-1.4-1.4L10 8.6 6.3 5.3z"/></svg>
                                <span class="sr-only">Hapus filter</span>
                            </button>
                        @endforeach
                        @if (count($chips) > 1)
                            <button type="button" data-clear-filters class="text-xs text-blue-400 hover:text-blue-300">Hapus semua</button>
                        @endif
                    </div>

                    @if ($products->isEmpty())
                        <x-empty-state title="Produk tidak ditemukan" icon="search">
                            Coba kata kunci lain atau kurangi filter yang dipakai.
                            <x-slot:action>
                                <button type="button" data-clear-filters class="btn btn-secondary">Hapus semua filter</button>
                            </x-slot:action>
                        </x-empty-state>
                    @else
                        <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-6 md:grid-cols-3">
                            @foreach ($products as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>

                        <div class="mt-12">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('filter-form');
            const searchInput = document.getElementById('search-input');
            const sortSelect = document.getElementById('sort-select');
            const area = document.getElementById('ajax-product-area');
            const filterCount = document.getElementById('filter-count');
            let typingTimer;
            let skeletonTimer;
            let controller;

            // Kerangka kartu abu-abu selama hasil dimuat
            const skeleton = `
                <div class="mb-6 h-5 w-24 animate-pulse rounded bg-zinc-900"></div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-6 md:grid-cols-3">
                    ${'<div><div class="aspect-[4/5] animate-pulse rounded-xl bg-zinc-900"></div><div class="mt-3 h-4 w-3/4 animate-pulse rounded bg-zinc-900"></div><div class="mt-2 h-4 w-1/3 animate-pulse rounded bg-zinc-900"></div></div>'.repeat(6)}
                </div>`;

            const setLoading = (loading) => {
                clearTimeout(skeletonTimer);
                area.setAttribute('aria-busy', loading ? 'true' : 'false');
                if (loading) {
                    // Hanya tampil kalau respons lebih lambat dari 150 ms, supaya tidak berkedip
                    skeletonTimer = setTimeout(() => (area.innerHTML = skeleton), 150);
                }
            };

            const fetchProducts = async (url, { scroll = false } = {}) => {
                controller?.abort();
                controller = new AbortController();
                setLoading(true);
                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal });
                    if (!response.ok) throw new Error(response.statusText);
                    const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                    setLoading(false);
                    area.innerHTML = doc.getElementById('ajax-product-area').innerHTML;
                    history.pushState({}, '', url);

                    const count = Number(area.querySelector('[data-filter-count]')?.dataset.filterCount || 0);
                    filterCount.textContent = count;
                    filterCount.classList.toggle('hidden', count === 0);

                    if (scroll) area.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (error) {
                    if (error.name !== 'AbortError') window.location.href = url;
                }
            };

            const update = () => {
                const params = new URLSearchParams(new FormData(form));
                params.set('sort', sortSelect.value);
                for (const [key, value] of [...params]) {
                    if (value === '') params.delete(key);
                }
                fetchProducts(`${form.action}?${params}`);
            };

            // Kosongkan kata kunci tanpa memicu pencarian ganda dari listener "input"
            const clearSearch = () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input')); // sinkronkan tombol × (Alpine)
            };

            const resetPrice = () => {
                const all = document.getElementById('price-all');
                if (all) all.checked = true;
            };

            searchInput.addEventListener('input', (event) => {
                if (!event.isTrusted) return;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(update, 400);
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                clearTimeout(typingTimer);
                update();
            });

            document.addEventListener('change', (event) => {
                const target = event.target;
                if (target === sortSelect || target.classList.contains('filter-checkbox')) update();
            });

            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-clear-search]')) {
                    clearTimeout(typingTimer);
                    clearSearch();
                    searchInput.focus();
                    return update();
                }

                const chip = event.target.closest('[data-uncheck]');
                if (chip) {
                    const input = document.getElementById(chip.dataset.uncheck);
                    if (input === searchInput) clearSearch();
                    else if (input?.type === 'radio') resetPrice();
                    else if (input) input.checked = false;
                    return update();
                }

                if (event.target.closest('[data-clear-filters]')) {
                    form.querySelectorAll('input[type="checkbox"]').forEach((cb) => (cb.checked = false));
                    resetPrice();
                    clearSearch();
                    return update();
                }

                const pageLink = event.target.closest('#ajax-product-area nav[role="navigation"] a');
                if (pageLink) {
                    event.preventDefault();
                    fetchProducts(pageLink.href, { scroll: true });
                }
            });

            window.addEventListener('popstate', () => window.location.reload());
        });
    </script>
@endpush
