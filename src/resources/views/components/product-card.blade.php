@props(['product'])

@php
    $image = $product->images->firstWhere('is_primary', 1) ?? $product->images->first();
    $hoverImage = $product->images->first(fn ($img) => $img->id_image !== $image?->id_image);
    $stock = $product->total_stok ?? null;
    $soldOut = $stock !== null && (int) $stock <= 0;
@endphp

<a href="{{ route('product.show', $product->nama_product) }}"
   class="group flex flex-col rounded-2xl focus-visible:outline-offset-4">
    <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-zinc-900">
        <img src="{{ \App\Support\ImageUrl::make($image?->url_gambar, 600) }}"
             alt="{{ $product->nama_product }}"
             loading="lazy" decoding="async"
             onerror="this.onerror=null;this.src='{{ \App\Support\ImageUrl::placeholder() }}'"
             class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03] {{ $soldOut ? 'opacity-50 grayscale' : '' }}">

        {{-- Foto kedua muncul saat kursor diarahkan (hanya di perangkat yang punya hover) --}}
        @if ($hoverImage && ! $soldOut)
            <img src="{{ \App\Support\ImageUrl::make($hoverImage->url_gambar, 600) }}"
                 alt="" aria-hidden="true" loading="lazy" decoding="async"
                 onerror="this.remove()"
                 class="pointer-events-none absolute inset-0 hidden h-full w-full object-cover opacity-0 transition-opacity duration-300 group-hover:opacity-100 [@media(hover:hover)]:block">
        @endif

        @if ($soldOut)
            <span class="badge badge-neutral absolute top-3 left-3 bg-zinc-950/80">Stok habis</span>
        @elseif ($stock !== null && (int) $stock <= 5)
            <span class="badge badge-warning absolute top-3 left-3 bg-zinc-950/80">Sisa {{ (int) $stock }}</span>
        @endif
    </div>

    <div class="mt-3 flex flex-col gap-1 px-0.5">
        <h3 class="line-clamp-1 text-sm font-medium text-zinc-200 transition-colors group-hover:text-white sm:text-base" title="{{ $product->nama_product }}">
            {{ $product->nama_product }}
        </h3>
        <p class="text-sm text-zinc-400">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
    </div>
</a>
