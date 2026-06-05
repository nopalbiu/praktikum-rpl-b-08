<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $sizes = DB::table('sizes')->pluck('id_size');

        $types = [
            ['name' => 'Basic T-Shirt', 'cat' => 1, 'mat' => 'Cotton Combed 30s', 'price' => 120000],
            ['name' => 'Oversized Tee', 'cat' => 1, 'mat' => 'Cotton Combed 24s', 'price' => 150000],
            ['name' => 'Polo Shirt', 'cat' => 1, 'mat' => 'Pique Cotton', 'price' => 175000],
            ['name' => 'Denim Pants', 'cat' => 2, 'mat' => 'Denim', 'price' => 250000],
            ['name' => 'Cargo Pants', 'cat' => 2, 'mat' => 'Twill', 'price' => 280000],
            ['name' => 'Chino Pants', 'cat' => 2, 'mat' => 'Cotton Twill', 'price' => 220000],
            ['name' => 'Windbreaker Jacket', 'cat' => 3, 'mat' => 'Parachute', 'price' => 300000],
            ['name' => 'Denim Jacket', 'cat' => 3, 'mat' => 'Denim', 'price' => 350000],
            ['name' => 'Varsity Jacket', 'cat' => 3, 'mat' => 'Fleece & Leather', 'price' => 450000],
            ['name' => 'Classic Cap', 'cat' => 4, 'mat' => 'Canvas', 'price' => 85000],
            ['name' => 'Knit Beanie', 'cat' => 4, 'mat' => 'Knit', 'price' => 75000],
            ['name' => 'Sling Bag', 'cat' => 4, 'mat' => 'Cordura', 'price' => 150000],
        ];

        $colors = ['Black', 'White', 'Navy', 'Olive', 'Grey', 'Maroon', 'Khaki', 'Brown'];

        $count = 0;

        foreach ($types as $type) {
            foreach ($colors as $color) {
                if ($count >= 32) {
                    break 2;
                }

                $hargaAcak = $type['price'] + (rand(-2, 5) * 10000);

                $productId = DB::table('products')->insertGetId([
                    'nama_product' => $type['name'] . ' ' . $color,
                    'deskripsi' => 'Produk ' . $type['name'] . ' berkualitas tinggi dengan varian warna ' . $color . '.',
                    'material_pakaian' => $type['mat'],
                    'url_size_chart' => 'size-chart.png',
                    'harga' => $hargaAcak,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('product_category')->insert([
                    ['id_product' => $productId, 'id_category' => $type['cat']]
                ]);

                DB::table('product_images')->insert([
                    ['id_product' => $productId, 'url_gambar' => strtolower(str_replace(' ', '-', $type['name'])) . '-' . strtolower($color) . '.png', 'is_primary' => 1]
                ]);

                foreach ($sizes as $size) {
                    DB::table('product_variants')->insert([
                        'id_product' => $productId,
                        'id_size' => $size,
                        'stok' => rand(0, 50),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $count++;
            }
        }
    }
}