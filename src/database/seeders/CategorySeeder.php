<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['nama_category' => 'T-Shirt'],
            ['nama_category' => 'Pants'],
            ['nama_category' => 'Jacket'],
            ['nama_category' => 'Accessories']
        ]);
    }
}
