<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

// Developer: Hesa Khansa Arka
// NIM: L0124158

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua kategori untuk ditampilkan di radio button sidebar
        $categories = Category::all();

        // 2. Mulai query pemanggilan produk
        $query = Product::query();

        // 3. AC-1 (US-01): Fitur Search berdasarkan nama produk
        if ($request->filled('search')) {
            $query->where('nama_product', 'like', '%' . $request->search . '%');
        }

        // 4. AC-1 (US-02): Fitur Filter berdasarkan kategori
        // Karena relasinya Many-to-Many, kita wajib menggunakan whereHas dan nama fungsi 'categories'
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id_category', $request->category_id);
            });
        }

        // 5. Fitur Sorting (Berdasarkan harga murah-mahal seperti di desainmu)
        if ($request->filled('sort_by')) {
            if ($request->sort_by == 'price_asc') {
                $query->orderBy('harga', 'asc');
            } elseif ($request->sort_by == 'price_desc') {
                $query->orderBy('harga', 'desc');
            }
        } else {
            // Default: Urutkan dari yang terbaru ditambahkan
            $query->latest(); 
        }

        // 6. Ambil datanya (Load relasi categories dan primaryImage agar query lebih cepat)
        // Paginate 12 artinya menampilkan 12 produk per halaman
        $products = $query->with(['categories', 'primaryImage'])
                          ->paginate(12)
                          ->withQueryString();

        // 7. Lempar variabel $products dan $categories ke halaman index.blade.php
        return view('catalog.index', compact('products', 'categories'));
    }
}