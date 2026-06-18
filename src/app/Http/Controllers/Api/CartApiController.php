<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartApiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['id_user' => $user->id_user]);

        $items = CartItem::with(['variant.product.images', 'variant.size'])
            ->where('id_cart', $cart->id_cart)
            ->get();

        return response()->json($items->map(function ($item) {
            return [
                'id' => $item->id_cart_item,
                'quantity' => $item->qty,
                'product' => [
                    'id_product' => $item->variant->product->id_product,
                    'nama_product' => $item->variant->product->nama_product,
                    'harga' => (float)$item->variant->product->harga,
                    'images' => $item->variant->product->images->map(function ($img) {
                        return [
                            'id_image' => $img->id_image,
                            'url_gambar' => $img->url_gambar,
                            'is_primary' => (bool)$img->is_primary,
                        ];
                    }),
                ],
                'variant' => [
                    'id_variant' => $item->variant->id_variant,
                    'stok' => $item->variant->stok,
                    'size' => [
                        'id_size' => $item->variant->size->id_size,
                        'nama_size' => $item->variant->size->nama_size,
                    ],
                ]
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_variant' => 'required|exists:product_variants,id_variant',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['id_user' => $user->id_user]);

        $item = CartItem::where('id_cart', $cart->id_cart)
            ->where('id_variant', $request->id_variant)
            ->first();

        if ($item) {
            $item->qty += $request->quantity;
            $item->save();
        } else {
            $item = CartItem::create([
                'id_cart' => $cart->id_cart,
                'id_variant' => $request->id_variant,
                'qty' => $request->quantity,
            ]);
        }

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::findOrFail($id);
        $item->qty = $request->quantity;
        $item->save();

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
