<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = Order::where('id_user', $user->id_user)
                        ->with(['items.variant.product.images', 'items.variant.size', 'payment'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('user.profile', compact('user', 'orders'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Isi nama lengkap.',
        ]);

        $user = auth()->user();

        $user->nama = $request->nama;
        $user->save();

        return back()->with('success', 'Profil disimpan.');
    }

    public function updatePassword(Request $request)
    {
        // Error disimpan di bag "password" supaya muncul di form ganti password, bukan form profil.
        $request->validateWithBag('password', [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Isi password saat ini.',
            'password.required' => 'Isi password baru.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Ulangi password baru belum sama.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.'], 'password');
        }
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password diganti.');
    }
    
}