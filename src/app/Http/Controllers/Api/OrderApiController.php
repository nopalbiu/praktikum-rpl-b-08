<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ProductVariant;

class OrderApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string',
            'alamat_pengiriman' => 'required|string',
            'no_hp' => 'required|string',
            'kurir' => 'required|string',
            'metode_pembayaran' => 'required|string',
        ]);

        $user = Auth::user();
        $cart = Cart::where('id_user', $user->id_user)->first();

        if (!$cart || $cart->items()->count() == 0) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        return DB::transaction(function () use ($request, $user, $cart) {
            $totalHarga = 0;
            $items = $cart->items;

            foreach ($items as $item) {
                $totalHarga += $item->getSubtotal();
            }

            $alamatLengkap = "Nama: " . $request->nama_penerima . "\n" .
                             "No. HP: " . $request->no_hp . "\n" .
                             "Alamat: " . $request->alamat_pengiriman;

            if (!empty($request->catatan) && $request->catatan !== '-') {
                $alamatLengkap .= "\nCatatan: " . $request->catatan;
            }

            $ongkir = 0;
            if ($request->kurir == 'JNE') $ongkir = 15000;
            elseif ($request->kurir == 'J&T') $ongkir = 13000;
            elseif ($request->kurir == 'SiCepat') $ongkir = 12000;

            $admin = 0;
            if ($request->kurir == 'JNE') $admin = 3000;
            elseif ($request->kurir == 'J&T') $admin = 2500;
            elseif ($request->kurir == 'SiCepat') $admin = 2000;

            $grandTotal = $totalHarga + $ongkir + $admin;

            $order = Order::create([
                'id_user' => $user->id_user,
                'alamat_pengiriman' => $alamatLengkap,
                'kurir' => $request->kurir,
                'total_harga' => $grandTotal,
                'status_pesanan' => 'pending',
                'tanggal_order' => now(),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'id_order' => $order->id_order,
                    'id_variant' => $item->id_variant,
                    'qty' => $item->qty,
                    'harga_satuan' => $item->variant->product->harga,
                ]);

                // Potong stok
                $variant = $item->variant;
                $variant->stok -= $item->qty;
                $variant->save();
            }

            \App\Models\Payment::create([
                'id_order' => $order->id_order,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'belum_bayar',
                'jumlah_bayar' => $grandTotal,
                'waktu_transaksi' => now(),
            ]);

            CartItem::where('id_cart', $cart->id_cart)->delete();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => [
                    'id_order' => $order->id_order,
                    'total_harga' => (float)$order->total_harga,
                    'status' => $order->status_pesanan,
                    'created_at' => $order->created_at,
                    'metode_pembayaran' => $request->metode_pembayaran,
                ]
            ], 201);
        });
    }

    public function directBuy(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string',
            'alamat_pengiriman' => 'required|string',
            'no_hp' => 'required|string',
            'kurir' => 'required|string',
            'metode_pembayaran' => 'required|string',
            'id_variant' => 'required|integer',
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $variant = ProductVariant::with('product')->find($request->id_variant);

        if (!$variant || !$variant->product) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        if ($variant->stok < $request->qty) {
            return response()->json(['message' => 'Stok tidak mencukupi'], 400);
        }

        return DB::transaction(function () use ($request, $user, $variant) {
            $hargaSatuan = $variant->product->harga;
            $totalHarga = $hargaSatuan * $request->qty;

            $alamatLengkap = "Nama: " . $request->nama_penerima . "\n" .
                             "No. HP: " . $request->no_hp . "\n" .
                             "Alamat: " . $request->alamat_pengiriman;

            if (!empty($request->catatan) && $request->catatan !== '-') {
                $alamatLengkap .= "\nCatatan: " . $request->catatan;
            }

            $ongkir = 0;
            if ($request->kurir == 'JNE') $ongkir = 15000;
            elseif ($request->kurir == 'J&T') $ongkir = 13000;
            elseif ($request->kurir == 'SiCepat') $ongkir = 12000;

            $admin = 0;
            if ($request->kurir == 'JNE') $admin = 3000;
            elseif ($request->kurir == 'J&T') $admin = 2500;
            elseif ($request->kurir == 'SiCepat') $admin = 2000;

            $grandTotal = $totalHarga + $ongkir + $admin;

            $order = Order::create([
                'id_user' => $user->id_user,
                'alamat_pengiriman' => $alamatLengkap,
                'kurir' => $request->kurir,
                'total_harga' => $grandTotal,
                'status_pesanan' => 'pending',
                'tanggal_order' => now(),
            ]);

            OrderItem::create([
                'id_order' => $order->id_order,
                'id_variant' => $variant->id_variant,
                'qty' => $request->qty,
                'harga_satuan' => $hargaSatuan,
            ]);

            $variant->stok -= $request->qty;
            $variant->save();

            \App\Models\Payment::create([
                'id_order' => $order->id_order,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'belum_bayar',
                'jumlah_bayar' => $grandTotal,
                'waktu_transaksi' => now(),
            ]);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => [
                    'id_order' => $order->id_order,
                    'total_harga' => (float)$order->total_harga,
                    'status' => $order->status_pesanan,
                    'created_at' => $order->created_at,
                    'metode_pembayaran' => $request->metode_pembayaran,
                ]
            ], 201);
        });
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
