<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const COURIER_OPTIONS = [
        'JNE'     => ['ongkir' => 15000, 'asuransi' => 3000],
        'J&T'     => ['ongkir' => 13000, 'asuransi' => 2500],
        'SiCepat' => ['ongkir' => 12000, 'asuransi' => 2000],
    ];

    /**
     * Membuat pesanan dalam satu transaksi. Stok varian dikunci (SELECT ... FOR UPDATE)
     * lalu dicek ulang, sehingga dua checkout bersamaan tidak bisa membuat stok minus.
     *
     * @param  array<int,int>  $lines  [id_variant => qty]
     * @param  array{alamat_pengiriman:string,kurir:string,metode_pembayaran:string}  $data
     * @param  callable|null  $insideTransaction  dijalankan di dalam transaksi setelah order dibuat
     *                                            (misalnya untuk menghapus item keranjang)
     *
     * @throws InsufficientStockException
     */
    public function placeOrder(User $user, array $lines, array $data, ?callable $insideTransaction = null): Order
    {
        return DB::transaction(function () use ($user, $lines, $data, $insideTransaction) {
            $variants = ProductVariant::with(['product', 'size'])
                ->whereIn('id_variant', array_keys($lines))
                ->lockForUpdate()
                ->get()
                ->keyBy('id_variant');

            $totalItemPrice = 0;
            foreach ($lines as $idVariant => $qty) {
                $variant = $variants->get($idVariant);

                if (! $variant || ! $variant->is_active || ! $variant->product) {
                    throw new InsufficientStockException('Produk yang dipilih sudah tidak tersedia.');
                }

                if ($variant->stok < $qty) {
                    throw new InsufficientStockException(
                        "Stok tidak cukup untuk {$variant->product->nama_product} (Size: {$variant->size?->nama_size}). "
                        . "Stok tersedia: {$variant->stok}, diminta: {$qty}."
                    );
                }

                $totalItemPrice += $qty * (float) $variant->product->harga;
            }

            $courier = self::COURIER_OPTIONS[$data['kurir']];
            $totalHarga = $totalItemPrice + $courier['ongkir'] + $courier['asuransi'];

            $order = Order::create([
                'id_user'           => $user->id_user,
                'tanggal_order'     => now(),
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'kurir'             => $data['kurir'],
                'total_harga'       => $totalHarga,
                'status_pesanan'    => Order::STATUS_MENUNGGU_PEMBAYARAN,
            ]);

            foreach ($lines as $idVariant => $qty) {
                $variant = $variants->get($idVariant);

                OrderItem::create([
                    'id_order'     => $order->id_order,
                    'id_variant'   => $variant->id_variant,
                    'qty'          => $qty,
                    'harga_satuan' => $variant->product->harga,
                ]);

                $variant->decrement('stok', $qty);
            }

            Payment::create([
                'id_order'          => $order->id_order,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => 'belum_bayar',
                'jumlah_bayar'      => $totalHarga,
                'waktu_transaksi'   => now(),
            ]);

            if ($insideTransaction) {
                $insideTransaction($order);
            }

            return $order;
        });
    }

    public static function formatAlamat(string $nama, string $noHp, string $alamat, ?string $catatan = null): string
    {
        $text = "Nama: {$nama}\nNo. HP: {$noHp}\nAlamat: {$alamat}";

        if (! empty($catatan) && $catatan !== '-') {
            $text .= "\nCatatan: {$catatan}";
        }

        return $text;
    }
}
