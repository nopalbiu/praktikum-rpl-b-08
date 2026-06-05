<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Register untuk User
Route::get('/register', [AuthenticationController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthenticationController::class, 'processRegister']);

// Login User
Route::get('/login', [AuthenticationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthenticationController::class, 'processLogin']);

// Login khusus untuk Admin
Route::get('/admin/login', [AuthenticationController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthenticationController::class, 'processAdminLogin']);

// Route yang butuh login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Admin katalog
Route::get('/admin/katalog-pakaian', function () {
    return "Welcome Admin.";
})->name('admin.katalog');