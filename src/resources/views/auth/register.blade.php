@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <main class="container-page flex flex-1 items-center justify-center py-12 sm:py-20">
        <div class="w-full max-w-sm">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-white">Buat akun</h1>
                <p class="mt-2 text-sm text-zinc-500">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300">Masuk</a></p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="card mt-8 space-y-5 p-6 sm:p-8">
                @csrf

                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="input @error('name') input-error @enderror">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                           placeholder="nama@email.com" class="input @error('email') input-error @enderror">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <x-password-input autocomplete="new-password" required minlength="8" />
                    @error('password') <p class="field-error">{{ $message }}</p> @else <p class="mt-1.5 text-xs text-zinc-500">Minimal 8 karakter.</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Ulangi password</label>
                    <x-password-input name="password_confirmation" autocomplete="new-password" required />
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-full">Daftar</button>
            </form>
        </div>
    </main>
@endsection
