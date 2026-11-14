@props(['order'])

@php
    $variant = match ($order->status_pesanan) {
        \App\Models\Order::STATUS_MENUNGGU_PEMBAYARAN => 'badge-warning',
        \App\Models\Order::STATUS_SUDAH_DIBAYAR => 'badge-info',
        \App\Models\Order::STATUS_DALAM_PERJALANAN => 'badge-purple',
        \App\Models\Order::STATUS_SELESAI => 'badge-success',
        \App\Models\Order::STATUS_DIBATALKAN => 'badge-danger',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$variant}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $order->statusLabel() }}
</span>
