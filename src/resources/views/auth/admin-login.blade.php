@extends('layouts.base')

@section('title', 'Masuk admin')

@section('body-class', 'flex min-h-screen items-center justify-center p-4 font-sans')

@section('body')
    <main id="konten" class="w-full max-w-sm">
        <div class="text-center">
            <img src="{{ asset('images/logo-www.png') }}" alt="WearWoreWorn" class="mx-auto h-9 w-auto">
            <h1 class="mt-6 text-2xl font-bold tracking-tight text-white">Panel admin</h1>
            <p class="mt-2 text-sm text-zinc-500">Masuk dengan akun admin untuk mengelola toko.</p>
        </div>

        <form action="{{ route('admin.login') }}" method="POST" class="card mt-8 space-y-5 p-6 sm:p-8">
            @csrf

            @error('email')
                <div class="rounded-lg border border-red-900/60 bg-red-950/30 px-3.5 py-2.5 text-sm text-red-300" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="email" class="label">Email admin</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <x-password-input required />
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full">Masuk</button>
        </form>

        <p class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm text-zinc-500 hover:text-white">← Kembali ke toko</a>
        </p>
    </main>
@endsection
