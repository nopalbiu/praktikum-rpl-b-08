<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Menampilkan halaman detail produk
     */
    public function show($nama): View
    {
        $product = Product::with(['images' => function ($query) {
            $query->orderBy('is_primary', 'desc')->orderBy('id_image', 'asc');
        }])->where('nama_product', $nama)->firstOrFail();

        $variants = \Illuminate\Support\Facades\DB::table('product_variants')
            ->join('sizes', 'product_variants.id_size', '=', 'sizes.id_size')
            ->where('id_product', $product->id_product)
            ->get();

        $totalStock = $variants->sum('stok');

        return view('detail-produk', [
            'product' => $product,
            'variants' => $variants,
            'totalStock' => $totalStock
        ]);
    }

    /**
     * Menambahkan item ke keranjang (Session)
     */
    public function addToCart(Request $request, $productId): RedirectResponse
    {
        // Validasi input wajib dari form detail produk
        $request->validate([
            'size'     => 'required|in:S,M,L,XL,XXL',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product  = Product::findOrFail($productId);
        $quantity = (int) $request->input('quantity', 1);
        $size     = $request->input('size');

        // Mengambil keranjang dari session
        $cart    = session()->get('cart', []);
        $cartKey = $product->id_product . '_' . $size; 

        // Logika penambahan kuantitas jika barang sudah ada di keranjang
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            // Memasukkan data baru menggunakan nama kolom DB asli kalian
            $cart[$cartKey] = [
                'product_id'   => $product->id_product,
                'product_name' => $product->nama_product,
                'thumbnail'    => $product->url_gambar,
                'size'         => $size,
                'price'        => $product->harga,
                'quantity'     => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()
            ->with('success', "{$product->nama_product} (Size: {$size}) berhasil ditambahkan ke keranjang.");
    }

    /**
     * Langsung beli dan menuju Checkout
     */
    public function buyNow(Request $request, $productId): RedirectResponse
    {
        $request->validate([
            'size'     => 'required|in:S,M,L,XL,XXL',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product  = Product::findOrFail($productId);
        $quantity = (int) $request->input('quantity', 1);
        $size     = $request->input('size');

        $cartKey = $product->id_product . '_' . $size;
        
        // Untuk "Buy it now", keranjang bisa ditimpa atau ditambahkan. 
        // Di sini kita tambahkan saja ke cart yang sudah ada sebelum ke checkout
        $cart = session()->get('cart', []);
        $cart[$cartKey] = [
            'product_id'   => $product->id_product,
            'product_name' => $product->nama_product,
            'thumbnail'    => $product->url_gambar,
            'size'         => $size,
            'price'        => $product->harga,
            'quantity'     => $quantity,
        ];
        session()->put('cart', $cart);

        // Ganti 'checkout.index' dengan rute checkout kalian nanti
        return redirect()->route('checkout.index'); 
    }
}