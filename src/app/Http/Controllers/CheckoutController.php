<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const BANK_ACCOUNTS = [
        'Transfer Bank BCA'     => 'BCA 1234567890 a.n. WearWoreWorn',
        'Transfer Bank Mandiri' => 'Mandiri 0987654321 a.n. WearWoreWorn',
        'Transfer Bank BRI'     => 'BRI 1122334455 a.n. WearWoreWorn',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        $selectedItems = $request->input('items', []);
        if (empty($selectedItems) || !is_array($selectedItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Silakan pilih setidaknya satu barang untuk di-checkout.');
        }

        $cart = Cart::with(['items.variant.product.images', 'items.variant.size'])
                    ->where('id_user', $user->id_user)
                    ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        $cart->setRelation('items', $cart->items->filter(function($item) use ($selectedItems) {
            return in_array($item->id_cart_item, $selectedItems);
        }));

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Barang yang dipilih tidak valid atau sudah dihapus.');
        }

        $totalItemPrice = 0;
        $hasOutOfStock = false;

        foreach ($cart->items as $item) {
            $totalItemPrice += $item->qty * (float) $item->variant->product->harga;
            if ($item->variant->stok < $item->qty) {
                $hasOutOfStock = true;
            }
        }

        return view('checkout.index', [
            'cart'            => $cart,
            'user'            => $user,
            'totalItemPrice'  => $totalItemPrice,
            'courierOptions'  => OrderService::COURIER_OPTIONS,
            'bankAccounts'    => array_keys(self::BANK_ACCOUNTS),
            'hasOutOfStock'   => $hasOutOfStock,
            'selectedItems'   => $selectedItems,
        ]);
    }

    public function processOrder(Request $request, OrderService $orderService): RedirectResponse
    {
        $request->validate([
            'nama_penerima'      => 'required|string|max:255',
            'no_hp'              => 'required|string|regex:/^[0-9]+$/|min:11|max:20',
            'alamat'             => 'required|string|max:1000',
            'kurir'              => 'required|string|in:' . implode(',', array_keys(OrderService::COURIER_OPTIONS)),
            'metode_pembayaran'  => 'required|string|in:' . implode(',', array_keys(self::BANK_ACCOUNTS)),
            'items'              => 'required|array',
            'items.*'            => 'integer',
        ], [
            'nama_penerima.required'     => 'Isi nama penerima.',
            'no_hp.required'             => 'Isi nomor HP.',
            'no_hp.regex'                => 'Nomor HP hanya boleh berisi angka.',
            'no_hp.min'                  => 'Nomor HP minimal 11 digit.',
            'no_hp.max'                  => 'Nomor HP maksimal 20 digit.',
            'alamat.required'            => 'Isi alamat lengkap.',
            'kurir.required'             => 'Pilih kurir.',
            'kurir.in'                   => 'Pilih kurir dari daftar.',
            'metode_pembayaran.required' => 'Pilih metode pembayaran.',
            'metode_pembayaran.in'       => 'Pilih metode pembayaran dari daftar.',
            'items.required'             => 'Pilih barang yang mau di-checkout.',
        ]);

        $user = Auth::user();

        $cart = Cart::where('id_user', $user->id_user)->first();

        // Hanya item yang benar-benar ada di keranjang milik user ini yang diproses.
        $cartItems = $cart
            ? CartItem::where('id_cart', $cart->id_cart)
                ->whereIn('id_cart_item', $request->input('items', []))
                ->get()
            : collect();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Barang yang dipilih tidak valid atau keranjang kosong.');
        }

        $lines = [];
        foreach ($cartItems as $item) {
            $lines[$item->id_variant] = ($lines[$item->id_variant] ?? 0) + $item->qty;
        }

        try {
            $order = $orderService->placeOrder($user, $lines, [
                'alamat_pengiriman' => OrderService::formatAlamat(
                    $request->input('nama_penerima'),
                    $request->input('no_hp'),
                    $request->input('alamat'),
                ),
                'kurir'             => $request->input('kurir'),
                'metode_pembayaran' => $request->input('metode_pembayaran'),
            ], function () use ($cart, $cartItems) {
                CartItem::where('id_cart', $cart->id_cart)
                    ->whereIn('id_cart_item', $cartItems->pluck('id_cart_item'))
                    ->delete();
            });
        } catch (InsufficientStockException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.success', $order->id_order);
    }

    public function success($orderId): View
    {
        $user = Auth::user();

        $order = Order::with(['payment', 'items.variant.product'])
                      ->where('id_user', $user->id_user)
                      ->where('id_order', $orderId)
                      ->firstOrFail();

        $infoRekening = self::BANK_ACCOUNTS[$order->payment->metode_pembayaran]
                        ?? 'BCA 1234567890 a.n. WearWoreWorn';

        $batasPembayaran = \Carbon\Carbon::parse($order->tanggal_order)->addHours(24);

        return view('checkout.success', [
            'order'            => $order,
            'infoRekening'     => $infoRekening,
            'batasPembayaran'  => $batasPembayaran,
        ]);
    }
}
