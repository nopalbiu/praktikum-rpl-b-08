<?php
namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class CartController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $cart = Cart::with(['items.variant.product.images', 'items.variant.size'])
                    ->where('id_user', $user->id_user)
                    ->first();
        return view('cart.index', [
            'cart' => $cart,
        ]);
    }

    public function addToCart(Request $request, $productId): RedirectResponse
    {
        [$product, $variant, $quantity, $error] = $this->resolveVariant($request, $productId);
        if ($error) {
            return $error;
        }

        [, $capped] = $this->putInCart($variant, $quantity, replace: false);

        $message = "{$product->nama_product} (ukuran {$variant->size->nama_size}) masuk ke keranjang.";
        if ($capped) {
            $message .= " Jumlahnya disesuaikan dengan stok yang tersisa ({$variant->stok}).";
        }

        return redirect()->back()->with('success', $message);
    }

    public function buyNow(Request $request, $productId): RedirectResponse
    {
        [, $variant, $quantity, $error] = $this->resolveVariant($request, $productId);
        if ($error) {
            return $error;
        }

        // "Beli sekarang" memakai jumlah yang dipilih di halaman produk, bukan ditambah ke isi keranjang.
        [$item] = $this->putInCart($variant, $quantity, replace: true);

        return redirect()->route('checkout.index', ['items' => [$item->id_cart_item]]);
    }

    /** @return array{0: ?Product, 1: ?\App\Models\ProductVariant, 2: int, 3: ?RedirectResponse} */
    private function resolveVariant(Request $request, $productId): array
    {
        $request->validate([
            'size'     => 'required|string',
            'quantity' => 'required|integer|min:1|max:99',
        ], [
            'size.required' => 'Pilih ukuran dulu.',
        ]);

        $product = Product::findOrFail($productId);
        $size = $request->input('size');
        $variant = $product->getVariantBySize($size);

        if (! $variant || $variant->stok <= 0) {
            return [$product, null, 0, redirect()->back()->withErrors(['size' => "Ukuran {$size} sedang tidak tersedia."])];
        }

        return [$product, $variant->load('size'), (int) $request->input('quantity', 1), null];
    }

    /**
     * Masukkan varian ke keranjang user, jumlahnya dibatasi stok.
     *
     * @return array{0: CartItem, 1: bool} item dan apakah jumlahnya dipotong karena stok
     */
    private function putInCart($variant, int $quantity, bool $replace): array
    {
        $cart = Cart::firstOrCreate(['id_user' => Auth::user()->id_user]);

        $item = CartItem::firstOrNew([
            'id_cart'    => $cart->id_cart,
            'id_variant' => $variant->id_variant,
        ]);

        $wanted = $replace || ! $item->exists ? $quantity : $item->qty + $quantity;
        $item->qty = min($wanted, (int) $variant->stok);
        $item->save();

        return [$item, $item->qty < $wanted];
    }

    /** Ubah jumlah barang di keranjang (dipanggil dari stepper +/− di halaman keranjang). */
    public function updateQty(Request $request, $cartItemId): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $cartItem = CartItem::with('variant')
            ->where('id_cart_item', $cartItemId)
            ->whereHas('cart', fn ($q) => $q->where('id_user', $user->id_user))
            ->firstOrFail();

        $maxQty = max(1, min(99, (int) $cartItem->variant->stok));

        $data = $request->validate([
            'qty' => "required|integer|min:1|max:{$maxQty}",
        ], [
            'qty.max' => "Stok tersisa {$cartItem->variant->stok}.",
        ]);

        $cartItem->update(['qty' => $data['qty']]);

        if ($request->expectsJson()) {
            return response()->json(['qty' => $cartItem->qty, 'max' => $maxQty]);
        }

        return redirect()->back();
    }

    public function remove($cartItemId): RedirectResponse
    {
        $user = Auth::user();
        
        $cartItem = CartItem::where('id_cart_item', $cartItemId)
            ->whereHas('cart', function($query) use ($user) {
                $query->where('id_user', $user->id_user);
            })->firstOrFail();

        $cartItem->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari keranjang.');
    }
}
