@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1.5 text-sm text-zinc-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
