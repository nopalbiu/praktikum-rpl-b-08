<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Auth; 

class AuthenticationController extends Controller
{
    public function showRegister()
    {
        return view('authentication.register'); 
    }

    public function processRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', 
            'password' => 'required|min:6'
        ]);

        $user = new User();
        
        $user->nama = $request->name; 
        $user->email = $request->email;
        $user->password = Hash::make($request->password); 
        $user->id_role = 2; 

        $user->save(); 

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    public function showLogin()
    {
        return view('authentication.login');
    }

    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            // Jika cocok, segarkan session untuk keamanan login
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang kamu masukkan salah.',
        ]);
    }
}