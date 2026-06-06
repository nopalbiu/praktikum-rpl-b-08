<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminProductController;

// Halaman Home 
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [HomeController::class, 'index'])->name('catalog.index');

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
Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Rute Utama (Dipakai oleh Navbar)
    Route::get('/products', [AdminProductController::class, 'index'])->name('product.index');
    
    // 2. RUTE JEMBATAN (Ini yang dicari oleh controller Naufal setelah sukses login)
    Route::get('/katalog-redirect', function () {
        return redirect()->route('admin.product.index');
    })->name('katalog');
    
    // 3. Rute CRUD Lainnya
    Route::post('/products', [AdminProductController::class, 'store'])->name('product.store');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('product.destroy');
        
});
 
// Halaman Detail Produk
Route::get('/product/{nama}', [ProductController::class, 'show'])->name('product.show');
Route::post('/cart/add/{id}', [ProductController::class, 'addToCart'])->name('cart.add');