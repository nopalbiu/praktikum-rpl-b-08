@extends('layouts.admin')

@section('title', 'Produk')

@php
    use App\Support\ImageUrl;

    $productData = $products->getCollection()->mapWithKeys(function ($p) use ($allSizeId) {
        $image = $p->images->firstWhere('is_primary', 1) ?? $p->images->first();
        $variants = $p->variants->mapWithKeys(fn ($v) => [$v->id_size => (int) $v->stok]);

        return [$p->id_product => [
            'id' => $p->id_product,
            'action' => route('admin.product.update', $p->id_product),
            'nama_product' => $p->nama_product,
            'id_category' => (string) ($p->categories->first()?->id_category ?? ''),
            'deskripsi' => $p->deskripsi,
            'material_pakaian' => $p->material_pakaian,
            'harga' => (int) round($p->harga),
            'image' => ImageUrl::make($image?->url_gambar, 300),
            'multi' => $variants->keys()->contains(fn ($id) => (int) $id !== (int) $allSizeId),
            'stok_tunggal' => $variants[$allSizeId] ?? '',
            'stok_ukuran' => $variants->except($allSizeId),
        ]];
    });

    // Kalau validasi gagal, buka lagi modal yang tadi dipakai beserta isian lamanya.
    $reopen = null;
    if ($errors->any()) {
        $reopen = [
            'mode' => old('edit_id') ? 'edit' : 'create',
            'id' => old('edit_id'),
            'action' => old('edit_id') ? route('admin.product.update', old('edit_id')) : route('admin.product.store'),
            'nama_product' => old('nama_product', ''),
            'id_category' => (string) old('id_category', ''),
            'deskripsi' => old('deskripsi', ''),
            'material_pakaian' => old('material_pakaian', ''),
            'harga' => old('harga', ''),
            'image' => old('edit_id') ? ($productData[old('edit_id')]['image'] ?? null) : null,
            'multi' => (bool) old('is_multi_size'),
            'stok_tunggal' => old('stok_tunggal', ''),
            'stok_ukuran' => old('stok_ukuran', []),
        ];
    }
@endphp

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
          x-data="productAdmin({ products: @js($productData), reopen: @js($reopen), storeUrl: @js(route('admin.product.store')) })">

        <x-page-header title="Produk" :subtitle="$products->total().' produk di katalog'">
            <form action="{{ route('admin.product.index') }}" method="GET" class="relative w-full sm:w-64" role="search">
                <label for="cari-produk" class="sr-only">Cari produk</label>
                <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z"/></svg>
                <input type="search" id="cari-produk" name="search" value="{{ $search }}" placeholder="Cari nama produk" class="input py-2 pl-9">
            </form>
            <button type="button" @click="openCreate()" class="btn btn-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
                Tambah produk
            </button>
        </x-page-header>

        @if ($search)
            <p class="-mt-4 mb-6 text-sm text-zinc-500">
                Hasil untuk “<span class="text-zinc-300">{{ $search }}</span>” ·
                <a href="{{ route('admin.product.index') }}" class="text-blue-400 hover:text-blue-300">Hapus pencarian</a>
            </p>
        @endif

        @if ($products->isEmpty())
            <x-empty-state :title="$search ? 'Produk tidak ditemukan' : 'Belum ada produk'" :icon="$search ? 'search' : 'box'">
                {{ $search ? 'Coba kata kunci lain.' : 'Tambahkan produk pertama untuk mulai berjualan.' }}
            </x-empty-state>
        @else
            {{-- Tabel (desktop) --}}
            <div class="hidden overflow-hidden rounded-2xl border border-zinc-800 md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-800 bg-zinc-900/60 text-xs text-zinc-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Produk</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 text-right font-medium">Harga</th>
                            <th class="px-4 py-3 text-right font-medium">Stok</th>
                            <th class="px-4 py-3 text-right font-medium"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/70">
                        @foreach ($products as $product)
                            @php
                                $stock = (int) $product->variants->sum('stok');
                                $image = $product->images->firstWhere('is_primary', 1) ?? $product->images->first();
                            @endphp
                            <tr class="transition-colors hover:bg-zinc-900/40">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ ImageUrl::make($image?->url_gambar, 100) }}" alt="" loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                                             class="h-12 w-12 shrink-0 rounded-lg bg-zinc-900 object-cover">
                                        <div class="min-w-0">
                                            <a href="{{ route('product.show', $product->nama_product) }}" target="_blank" class="line-clamp-1 font-medium text-white hover:underline">{{ $product->nama_product }}</a>
                                            <p class="line-clamp-1 max-w-sm text-xs text-zinc-500">{{ $product->deskripsi }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($product->categories->isNotEmpty())
                                        <span class="badge badge-neutral">{{ $product->categories->first()->nama_category }}</span>
                                    @else
                                        <span class="text-zinc-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap text-zinc-200">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-medium {{ $stock <= 0 ? 'text-red-400' : ($stock <= 5 ? 'text-amber-400' : 'text-zinc-200') }}">{{ $stock }}</span>
                                    @if ($stock <= 0) <span class="block text-xs text-red-400/80">Habis</span>
                                    @elseif ($stock <= 5) <span class="block text-xs text-amber-400/80">Menipis</span> @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <button type="button" @click="openEdit({{ $product->id_product }})" class="btn btn-ghost btn-sm" aria-label="Edit {{ $product->nama_product }}">Edit</button>
                                        <form action="{{ route('admin.product.destroy', $product->id_product) }}" method="POST"
                                              data-confirm="Produk “{{ $product->nama_product }}” akan dihapus permanen." data-confirm-title="Hapus produk?" data-confirm-button="Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm text-red-300 hover:text-red-200" aria-label="Hapus {{ $product->nama_product }}">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu (mobile) --}}
            <ul class="space-y-3 md:hidden">
                @foreach ($products as $product)
                    @php
                        $stock = (int) $product->variants->sum('stok');
                        $image = $product->images->firstWhere('is_primary', 1) ?? $product->images->first();
                    @endphp
                    <li class="card flex gap-3 p-3">
                        <img src="{{ ImageUrl::make($image?->url_gambar, 150) }}" alt="" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ ImageUrl::placeholder() }}'"
                             class="h-20 w-20 shrink-0 rounded-lg bg-zinc-900 object-cover">
                        <div class="flex min-w-0 flex-1 flex-col">
                            <p class="line-clamp-1 font-medium text-white">{{ $product->nama_product }}</p>
                            <p class="text-sm text-zinc-400">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                            <p class="text-xs {{ $stock <= 0 ? 'text-red-400' : ($stock <= 5 ? 'text-amber-400' : 'text-zinc-500') }}">
                                Stok {{ $stock }}{{ $product->categories->isNotEmpty() ? ' · '.$product->categories->first()->nama_category : '' }}
                            </p>
                            <div class="mt-auto flex gap-1 pt-2">
                                <button type="button" @click="openEdit({{ $product->id_product }})" class="btn btn-secondary btn-sm">Edit</button>
                                <form action="{{ route('admin.product.destroy', $product->id_product) }}" method="POST"
                                      data-confirm="Produk “{{ $product->nama_product }}” akan dihapus permanen." data-confirm-title="Hapus produk?" data-confirm-button="Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red-300">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">{{ $products->links() }}</div>
        @endif

        {{-- Modal tambah / edit --}}
        <div x-cloak x-show="modal" x-transition.opacity class="fixed inset-0 z-[60] flex justify-end bg-black/60" @keydown.escape.window="modal = false">
            <div x-show="modal" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-x-full"
                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="translate-x-full"
                 @click.outside="modal = false"
                 class="flex h-full w-full max-w-lg flex-col border-l border-zinc-800 bg-zinc-950" role="dialog" aria-modal="true" aria-labelledby="judul-form">

                <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4">
                    <h2 id="judul-form" class="text-lg font-semibold text-white" x-text="mode === 'edit' ? 'Edit produk' : 'Tambah produk'"></h2>
                    <button type="button" @click="modal = false" class="btn btn-ghost -mr-2 h-9 w-9 p-0" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <form :action="form.action" method="POST" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <div>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="edit_id" :value="form.id">
                        </div>
                    </template>

                    <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                        @if ($errors->any())
                            <div class="rounded-lg border border-red-900/60 bg-red-950/30 p-3.5 text-sm text-red-300" role="alert">
                                <p class="font-medium">Periksa lagi isian berikut:</p>
                                <ul class="mt-1.5 list-disc space-y-0.5 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label for="f-nama" class="label">Nama produk</label>
                            <input type="text" id="f-nama" name="nama_product" x-model="form.nama_product" required maxlength="255" class="input @error('nama_product') input-error @enderror">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="f-kategori" class="label">Kategori</label>
                                <select id="f-kategori" name="id_category" x-model="form.id_category" required class="input @error('id_category') input-error @enderror">
                                    <option value="" disabled>Pilih kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id_category }}">{{ $cat->nama_category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="f-harga" class="label">Harga</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-sm text-zinc-500">Rp</span>
                                    <input type="number" id="f-harga" name="harga" x-model="form.harga" required min="0" step="1" inputmode="numeric" class="input pl-10 @error('harga') input-error @enderror">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="f-deskripsi" class="label">Deskripsi</label>
                            <textarea id="f-deskripsi" name="deskripsi" x-model="form.deskripsi" rows="4" required class="input resize-y @error('deskripsi') input-error @enderror"></textarea>
                        </div>

                        <div>
                            <label for="f-bahan" class="label">Bahan <span class="font-normal text-zinc-500">(opsional)</span></label>
                            <input type="text" id="f-bahan" name="material_pakaian" x-model="form.material_pakaian" maxlength="150" placeholder="Cotton combed 24s" class="input">
                        </div>

                        {{-- Foto --}}
                        <fieldset class="space-y-4 border-t border-zinc-800 pt-5">
                            <legend class="sr-only">Foto</legend>
                            <div>
                                <span class="label" x-text="mode === 'edit' ? 'Foto utama (kosongkan jika tidak diganti)' : 'Foto utama'"></span>
                                <label class="flex cursor-pointer items-center gap-4 rounded-xl border border-dashed border-zinc-700 p-3 hover:border-zinc-500">
                                    <img x-show="preview" :src="preview" alt="" class="h-16 w-16 rounded-lg object-cover">
                                    <span x-show="!preview" class="flex h-16 w-16 items-center justify-center rounded-lg bg-zinc-900 text-zinc-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                                    </span>
                                    <span class="text-sm text-zinc-400">
                                        <span class="text-white">Pilih foto</span> · JPG atau PNG, maks. 2MB
                                    </span>
                                    <input type="file" name="foto_utama" accept="image/jpeg,image/png" class="sr-only"
                                           :required="mode === 'create'" @change="pickMain($event)">
                                </label>
                            </div>
                            <div>
                                <label for="f-foto-tambahan" class="label" x-text="mode === 'edit' ? 'Foto tambahan (mengganti semua foto tambahan lama)' : 'Foto tambahan (opsional)'"></label>
                                <input type="file" id="f-foto-tambahan" name="foto_tambahan[]" accept="image/jpeg,image/png" multiple @change="extraCount = $event.target.files.length"
                                       class="block w-full text-sm text-zinc-400 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-800 file:px-3 file:py-2 file:text-sm file:text-zinc-200 hover:file:bg-zinc-700">
                                <p x-show="extraCount" class="mt-1.5 text-xs text-zinc-500" x-text="`${extraCount} foto dipilih`"></p>
                            </div>
                        </fieldset>

                        {{-- Stok --}}
                        <fieldset class="space-y-4 border-t border-zinc-800 pt-5">
                            <legend class="sr-only">Stok</legend>
                            <label class="flex cursor-pointer items-center justify-between gap-4">
                                <span>
                                    <span class="block text-sm font-medium text-zinc-200">Punya beberapa ukuran</span>
                                    <span class="block text-xs text-zinc-500">Matikan untuk produk satu ukuran (All size).</span>
                                </span>
                                <input type="checkbox" name="is_multi_size" value="1" x-model="form.multi" class="peer sr-only">
                                <span class="relative h-6 w-11 shrink-0 rounded-full bg-zinc-700 transition-colors peer-checked:bg-blue-600 peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5"></span>
                            </label>

                            <div x-show="!form.multi">
                                <label for="f-stok" class="label">Stok</label>
                                <input type="number" id="f-stok" name="stok_tunggal" x-model="form.stok_tunggal" min="0" step="1" inputmode="numeric"
                                       :required="!form.multi" :disabled="form.multi" class="input max-w-40 @error('stok_tunggal') input-error @enderror">
                            </div>

                            <div x-show="form.multi">
                                <span class="label">Stok per ukuran</span>
                                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                                    @foreach ($sizes as $size)
                                        <div>
                                            <label for="f-ukuran-{{ $size->id_size }}" class="mb-1 block text-center text-xs font-medium text-zinc-400">{{ $size->nama_size }}</label>
                                            <input type="number" id="f-ukuran-{{ $size->id_size }}" name="stok_ukuran[{{ $size->id_size }}]"
                                                   x-model="form.stok_ukuran['{{ $size->id_size }}']" min="0" step="1" inputmode="numeric" placeholder="–"
                                                   :disabled="!form.multi" class="input px-2 text-center">
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-zinc-500">Kosongkan ukuran yang tidak dijual.</p>
                            </div>
                        </fieldset>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-zinc-800 px-6 py-4">
                        <button type="button" @click="modal = false" class="btn btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-primary" x-text="mode === 'edit' ? 'Simpan perubahan' : 'Simpan produk'"></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            const blank = (storeUrl) => ({
                id: null, action: storeUrl, nama_product: '', id_category: '', deskripsi: '', material_pakaian: '',
                harga: '', image: null, multi: false, stok_tunggal: '', stok_ukuran: {},
            });

            Alpine.data('productAdmin', ({ products, reopen, storeUrl }) => ({
                modal: false,
                mode: 'create',
                form: blank(storeUrl),
                preview: null,
                extraCount: 0,

                init() {
                    if (reopen) {
                        this.mode = reopen.mode;
                        this.form = { ...blank(storeUrl), ...reopen, stok_ukuran: { ...(reopen.stok_ukuran || {}) } };
                        this.preview = reopen.image;
                        this.modal = true;
                    }
                },
                openCreate() {
                    this.mode = 'create';
                    this.form = blank(storeUrl);
                    this.preview = null;
                    this.extraCount = 0;
                    this.modal = true;
                },
                openEdit(id) {
                    const p = products[id];
                    this.mode = 'edit';
                    this.form = { ...blank(storeUrl), ...p, stok_ukuran: { ...(p.stok_ukuran || {}) } };
                    this.preview = p.image;
                    this.extraCount = 0;
                    this.modal = true;
                },
                pickMain(event) {
                    const file = event.target.files[0];
                    if (file) this.preview = URL.createObjectURL(file);
                },
            }));
        });
    </script>
@endpush
