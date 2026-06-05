<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;

// Halaman Home 
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

// Autentikasi User
Route::get('/register', [AuthenticationController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthenticationController::class, 'processRegister']);

Route::get('/login', [AuthenticationController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthenticationController::class, 'processLogin']);

// Autentikasi Admin
Route::get('/admin/login', [AuthenticationController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthenticationController::class, 'processAdminLogin']);

// Route yang butuh login (Customer)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Fitur Cart & Checkout 
    Route::get('/cart', fn() => view('cart.index'))->name('cart.index');
    Route::post('/products/{productId}/add-to-cart', [ProductController::class, 'addToCart'])->name('cart.add');
    Route::post('/products/{productId}/buy-now', [ProductController::class, 'buyNow'])->name('product.buy-now');
    Route::get('/checkout', fn() => view('checkout.index'))->name('checkout.index');
});

// Admin Katalog
Route::get('/admin/katalog-pakaian', function () {
    return "Welcome Admin.";
})->name('admin.katalog');
 
// Halaman Detail Produk
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('product.show');