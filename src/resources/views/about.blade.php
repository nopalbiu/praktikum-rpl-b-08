@extends('layouts.app')

@section('title', 'Tentang kami')

@php
    $values = [
        ['title' => 'Nyaman dipakai', 'text' => 'Bahan katun tebal dan potongan yang longgar, enak dipakai seharian.', 'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
        ['title' => 'Awet', 'text' => 'Jahitan ganda di bagian yang sering tertarik supaya bentuknya tetap rapi.', 'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
        ['title' => 'Gampang dipadukan', 'text' => 'Warna netral dan desain sederhana yang cocok dengan isi lemarimu.', 'icon' => 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42'],
    ];
@endphp

@section('content')
    <main class="container-page max-w-4xl pt-12 pb-8 sm:pt-20">
        <p class="text-xs font-semibold tracking-[0.2em] text-blue-400 uppercase">Tentang kami</p>
        <h1 class="mt-3 text-3xl leading-tight font-bold tracking-tight text-white sm:text-5xl">Pakaian sehari-hari yang dibuat dengan serius</h1>

        <div class="mt-8 space-y-5 text-base leading-relaxed text-zinc-400 sm:text-lg">
            <p>
                <span class="text-white">WearWoreWorn</span> lahir dari keinginan sederhana: punya pakaian esensial yang enak dipakai,
                tahan lama, dan tetap terlihat rapi. Kami memadukan gaya streetwear dengan bahan dan jahitan yang diperhatikan detailnya.
            </p>
            <p>
                Kami tidak mengejar tren yang cepat berganti. Fokus kami adalah potongan yang awet, rilisan yang terencana,
                dan pakaian yang bisa kamu pakai dari tahun ke tahun.
            </p>
        </div>

        <div class="mt-14 grid gap-4 sm:grid-cols-3">
            @foreach ($values as $value)
                <div class="card p-6">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $value['icon'] }}"/></svg>
                    <h2 class="mt-4 text-base font-semibold text-white">{{ $value['title'] }}</h2>
                    <p class="mt-1.5 text-sm text-zinc-400">{{ $value['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-14 flex flex-col items-start gap-4 rounded-2xl border border-zinc-800 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
            <div>
                <h2 class="text-lg font-semibold text-white">Lihat koleksinya</h2>
                <p class="mt-1 text-sm text-zinc-400">Kaos, kemeja, hoodie, celana, dan aksesori.</p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-primary">Buka katalog</a>
        </div>
    </main>
@endsection
