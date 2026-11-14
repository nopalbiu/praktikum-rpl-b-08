@props(['current' => 1])

@php
    $steps = [1 => 'Keranjang', 2 => 'Pengiriman', 3 => 'Pembayaran'];
@endphp

<ol class="mb-8 flex items-center gap-2 text-xs sm:text-sm" aria-label="Langkah checkout">
    @foreach ($steps as $n => $label)
        <li class="flex items-center gap-2 {{ $n <= $current ? 'text-white' : 'text-zinc-600' }}" @if ($n === $current) aria-current="step" @endif>
            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-semibold
                         {{ $n < $current ? 'bg-white text-zinc-950' : ($n === $current ? 'border border-white' : 'border border-zinc-700') }}">
                @if ($n < $current)
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                @else
                    {{ $n }}
                @endif
            </span>
            {{ $label }}
        </li>
        @if (! $loop->last)
            <li aria-hidden="true" class="h-px w-6 bg-zinc-800 sm:w-10"></li>
        @endif
    @endforeach
</ol>
