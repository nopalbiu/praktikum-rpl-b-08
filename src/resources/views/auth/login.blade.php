@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <main class="container-page flex flex-1 items-center justify-center py-12 sm:py-20">
        <div class="w-full max-w-sm">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-white">Masuk ke akunmu</h1>
                <p class="mt-2 text-sm text-zinc-500">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300">Daftar sekarang</a></p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="card mt-8 space-y-5 p-6 sm:p-8">
                @csrf

                @error('email')
                    <div class="rounded-lg border border-red-900/60 bg-red-950/30 px-3.5 py-2.5 text-sm text-red-300" role="alert">{{ $message }}</div>
                @enderror

                <div>
                    <label for="email" class="label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           placeholder="nama@email.com" class="input">
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <x-password-input required />
                </div>

                <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-400">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-zinc-700 bg-zinc-900 accent-blue-500" @checked(old('remember'))>
                    Ingat saya
                </label>

                <button type="submit" class="btn btn-primary btn-lg w-full">Masuk</button>
            </form>
        </div>
    </main>
@endsection
