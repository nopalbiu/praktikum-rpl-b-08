<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function processRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Isi nama lengkap.',
            'email.required'     => 'Isi email.',
            'email.email'        => 'Format email belum benar.',
            'email.unique'       => 'Email ini sudah terdaftar. Coba masuk.',
            'password.required'  => 'Isi password.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Ulangi password belum sama.',
        ]);

        $user = new User();
        $user->nama = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->id_role = 2;
        $user->save();

        // Langsung masuk setelah daftar, tidak perlu login ulang.
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', "Selamat datang, {$user->nama}! Akunmu sudah jadi.");
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Isi email.',
            'email.email'       => 'Format email belum benar.',
            'password.required' => 'Isi password.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()->onlyInput('email', 'remember')->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function processAdminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->id_role == 1) {
                $request->session()->regenerate();

                return redirect()->route('admin.product.index');
            }

            Auth::logout();

            return back()->onlyInput('email')->withErrors([
                'email' => 'Akun ini bukan akun admin.',
            ]);
        }

        return back()->onlyInput('email')->withErrors([
            'email' => 'Email atau password admin salah.',
        ]);
    }

    public function logout(Request $request)
    {
        $isAdmin = Auth::user()?->id_role == 1;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $isAdmin
            ? redirect()->route('admin.login')
            : redirect()->route('home')->with('success', 'Kamu sudah keluar.');
    }
}
