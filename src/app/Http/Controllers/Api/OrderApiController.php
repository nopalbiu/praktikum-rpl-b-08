<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderApiController extends Controller
{
    public function store(Request $request, OrderService $orderService)
    {
        $request->validate($this->checkoutRules());

        $user = Auth::user();
        $cart = Cart::where('id_user', $user->id_user)->first();
        $items = $cart ? $cart->items()->get() : collect();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $lines = [];
        foreach ($items as $item) {
            $lines[$item->id_variant] = ($lines[$item->id_variant] ?? 0) + $item->qty;
        }

        return $this->placeOrder($orderService, $request, $lines, function () use ($cart, $items) {
            CartItem::where('id_cart', $cart->id_cart)
                ->whereIn('id_cart_item', $items->pluck('id_cart_item'))
                ->delete();
        });
    }

    public function directBuy(Request $request, OrderService $orderService)
    {
        $request->validate($this->checkoutRules() + [
            'id_variant' => 'required|integer',
            'qty'        => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with('product')->find($request->id_variant);

        if (! $variant || ! $variant->product || ! $variant->is_active) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        return $this->placeOrder($orderService, $request, [$variant->id_variant => (int) $request->qty]);
    }

    private function checkoutRules(): array
    {
        return [
            'nama_penerima'     => 'required|string',
            'alamat_pengiriman' => 'required|string',
            'no_hp'             => 'required|string',
            'kurir'             => 'required|string|in:' . implode(',', array_keys(OrderService::COURIER_OPTIONS)),
            'metode_pembayaran' => 'required|string',
        ];
    }

    private function placeOrder(OrderService $orderService, Request $request, array $lines, ?callable $insideTransaction = null)
    {
        try {
            $order = $orderService->placeOrder(Auth::user(), $lines, [
                'alamat_pengiriman' => OrderService::formatAlamat(
                    $request->nama_penerima,
                    $request->no_hp,
                    $request->alamat_pengiriman,
                    $request->catatan,
                ),
                'kurir'             => $request->kurir,
                'metode_pembayaran' => $request->metode_pembayaran,
            ], $insideTransaction);
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Order created successfully',
            'order' => [
                'id_order'          => $order->id_order,
                'total_harga'       => (float) $order->total_harga,
                'status'            => $order->status_pesanan,
                'created_at'        => $order->created_at,
                'metode_pembayaran' => $request->metode_pembayaran,
            ],
        ], 201);
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('id_user', $user->id_user)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders->map(function($o) {
            return [
                'id_order' => $o->id_order,
                'total_harga' => (float)$o->total_harga,
                'status' => $o->status_pesanan,
                'created_at' => $o->created_at,
                'metode_pembayaran' => $o->payment?->metode_pembayaran,
            ];
        }));
    }
    public function show($id)
    {
        $user = Auth::user();
        $order = Order::with('items.variant.product', 'payment')->where('id_user', $user->id_user)->where('id_order', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $alamat = $order->alamat_pengiriman;
        $parts = explode(' | ', $alamat);
        $namaPenerima = '-';
        $noHp = '-';
        $alamatPengiriman = $alamat;

        foreach ($parts as $part) {
            if (str_starts_with($part, 'Nama: ')) $namaPenerima = substr($part, 6);
            elseif (str_starts_with($part, 'HP: ')) $noHp = substr($part, 4);
            elseif (str_starts_with($part, 'Alamat: ')) $alamatPengiriman = substr($part, 8);
        }

        $ongkir = 0;
        $asuransi = 0;
        if ($order->kurir == 'JNE') { $ongkir = 15000; $asuransi = 3000; }
        elseif ($order->kurir == 'J&T') { $ongkir = 13000; $asuransi = 2500; }
        elseif ($order->kurir == 'SiCepat') { $ongkir = 12000; $asuransi = 2000; }

        $subtotalProduk = $order->total_harga - $ongkir - $asuransi;

        $items = $order->items->map(function($item) {
            $productName = $item->variant && $item->variant->product ? $item->variant->product->nama_product : '-';
            $size = $item->variant && $item->variant->size ? $item->variant->size->nama_size : '-';
            return [
                'id' => $item->id_order_item ?? rand(1,9999),
                'product_name' => $productName,
                'size' => $size,
                'quantity' => $item->qty,
                'price' => (float)$item->harga_satuan,
                'subtotal' => (float)($item->qty * $item->harga_satuan),
            ];
        });

        return response()->json([
            'id_order' => $order->id_order,
            'total_harga' => (float)$order->total_harga,
            'status' => $order->status_pesanan,
            'created_at' => $order->created_at,
            'metode_pembayaran' => $order->payment?->metode_pembayaran,
            'nama_penerima' => $namaPenerima,
            'no_hp' => $noHp,
            'alamat_pengiriman' => $alamatPengiriman,
            'kurir' => $order->kurir,
            'subtotal_produk' => (float)$subtotalProduk,
            'ongkir' => (float)$ongkir,
            'asuransi' => (float)$asuransi,
            'items' => $items
        ]);
    }
}
