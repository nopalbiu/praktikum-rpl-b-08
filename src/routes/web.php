<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

Route::inertia('/', 'welcome')->name('home');

// register untuk userRoute::get('/register', [AuthenticationController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthenticationController::class, 'processRegister']);

// login User 
Route::get('/login', [AuthenticationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthenticationController::class, 'processLogin']);

// login khusus untuk admin
Route::get('/admin/login', [AuthenticationController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthenticationController::class, 'processAdminLogin']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/admin/katalog-pakaian', function() {
    return "Welcome adminWWW.";
})->name('admin.katalog');

require __DIR__.'/settings.php';