<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController; // <-- Panggil Controller-mu di sini

Route::inertia('/', 'welcome')->name('home');

// Rute untuk pendaftaran User baru
Route::get('/register', [AuthenticationController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthenticationController::class, 'processRegister']);

// Rute untuk masuk (Login) User & Admin
Route::get('/login', [AuthenticationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthenticationController::class, 'processLogin']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';