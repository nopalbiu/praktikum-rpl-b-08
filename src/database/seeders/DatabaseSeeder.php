<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder buatan kita di sini secara berurutan
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}