<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - Menyamakan format status pesanan lama (spasi / "pending") ke format underscore.
     * - order_items.id_variant tidak lagi ikut terhapus (cascade) saat varian dihapus,
     *   supaya riwayat pesanan tidak hilang ketika admin mengedit / menghapus produk.
     * - Menambah product_variants.is_active untuk "menonaktifkan" varian yang sudah
     *   dipakai di pesanan alih-alih menghapusnya.
     */
    public function up(): void
    {
        $statusMap = [
            'pending'             => 'menunggu_pembayaran',
            'menunggu pembayaran' => 'menunggu_pembayaran',
            'sudah dibayar'       => 'sudah_dibayar',
            'dalam perjalanan'    => 'dalam_perjalanan',
        ];
        foreach ($statusMap as $old => $new) {
            DB::table('orders')->where('status_pesanan', $old)->update(['status_pesanan' => $new]);
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('stok');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['id_variant']);
            $table->foreign('id_variant')->references('id_variant')->on('product_variants')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['id_variant']);
            $table->foreign('id_variant')->references('id_variant')->on('product_variants')->onDelete('cascade');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
