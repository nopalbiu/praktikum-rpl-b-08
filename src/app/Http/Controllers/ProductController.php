<?php

namespace App\Http\Controllers;
 
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
 
class ProductController extends Controller
{

    public function show(string $slug): View
    {
        // Ambil produk berdasarkan slug + eager load relasi — 404 jika tidak ada
        $product = Product::with(['category', 'variants'])
            ->where('slug', $slug)
            ->firstOrFail();
 
        // Urutkan varian dari kecil ke besar (S → XXL) untuk tampilan size chart
        $sizeOrder = ['S', 'M', 'L', 'XL', 'XXL'];
        $variants  = $product->variants->sortBy(function ($variant) use ($sizeOrder) {
            return array_search($variant->size, $sizeOrder);
        })->values();
 
        return view('product.show', [
            'product'  => $product,
            'variants' => $variants,  // US-03: data size chart
        ]);
    }
 

    public function addToCart(Request $request, int $productId): RedirectResponse
    {
        // Validasi input wajib dari form detail produk
        $request->validate([
            'size'     => 'required|in:S,M,L,XL,XXL',
            'quantity' => 'required|integer|min:1|max:99',
        ]);
 
        $product  = Product::findOrFail($productId);
        $variant  = $product->getVariantBySize($request->input('size'));
        $quantity = (int) $request->input('quantity', 1);
 
        // Varian ukuran tidak ada di database
        if (!$variant) {
            return back()->with('error', 'Ukuran yang dipilih tidak tersedia.');
        }
 
        if (!$variant->isInStock()) {
            return back()->with('error', 'Stok Habis');
        }
 
        $cart    = session()->get('cart', []);
        $cartKey = $productId . '_' . $request->input('size'); 
 
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = min($cart[$cartKey]['quantity'] + $quantity, $variant->stock);
        } else {
            $cart[$cartKey] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'slug'         => $product->slug,
                'thumbnail'    => $product->thumbnail,
                'size'         => $request->input('size'),
                'price'        => $variant->getFinalPrice(),
                'quantity'     => min($quantity, $variant->stock),
            ];
        }
 
        session()->put('cart', $cart);
 
        return redirect()->route('cart.index')
            ->with('success', "{$product->name} (Size: {$request->input('size')}) berhasil ditambahkan ke keranjang.");
    }
 

    public function buyNow(Request $request, int $productId): RedirectResponse
    {
        $request->validate([
            'size'     => 'required|in:S,M,L,XL,XXL',
            'quantity' => 'required|integer|min:1|max:99',
        ]);
 
        $product  = Product::findOrFail($productId);
        $variant  = $product->getVariantBySize($request->input('size'));
        $quantity = (int) $request->input('quantity', 1);
 
        if (!$variant)           return back()->with('error', 'Ukuran yang dipilih tidak tersedia.');
        if (!$variant->isInStock()) return back()->with('error', 'Stok Habis');
 
        // Untuk "Buy it now": timpa cart dengan hanya item ini
        $cartKey = $productId . '_' . $request->input('size');
        session()->put('cart', [
            $cartKey => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'slug'         => $product->slug,
                'thumbnail'    => $product->thumbnail,
                'size'         => $request->input('size'),
                'price'        => $variant->getFinalPrice(),
                'quantity'     => min($quantity, $variant->stock),
            ],
        ]);
 
        // Langsung ke halaman checkout
        return redirect()->route('checkout.index');
    }
}