<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Memastikan setiap halaman utama bisa dirender tanpa error. */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([RoleSeeder::class, SizeSeeder::class]);

        $this->product = Product::create(['nama_product' => 'Kaos Polos', 'deskripsi' => 'Katun', 'harga' => 150000]);
        $this->product->allVariants()->create(['id_size' => 1, 'stok' => 5]);
        $this->product->images()->create(['url_gambar' => 'https://example.com/a.jpg', 'is_primary' => 1]);
    }

    public function test_halaman_publik_bisa_dibuka(): void
    {
        foreach (['/', '/catalog', '/about', '/login', '/register', '/admin/login'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/product/Kaos Polos')->assertOk()->assertSee('Kaos Polos');
    }

    public function test_halaman_customer_bisa_dibuka(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['id_user' => $user->id_user]);
        $item = CartItem::create(['id_cart' => $cart->id_cart, 'id_variant' => $this->product->variants->first()->id_variant, 'qty' => 1]);

        $this->actingAs($user)->get('/cart')->assertOk()->assertSee('Kaos Polos');
        $this->actingAs($user)->get('/checkout?items[]='.$item->id_cart_item)->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_halaman_admin_bisa_dibuka_dan_customer_ditolak(): void
    {
        $admin = User::factory()->create(['id_role' => 1]);
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();

        $this->actingAs(User::factory()->create())->get('/admin/products')->assertRedirect('/');
    }
}
