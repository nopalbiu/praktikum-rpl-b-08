<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKOUT_FORM = [
        'nama_penerima'     => 'Budi',
        'no_hp'             => '081234567890',
        'alamat'            => 'Jl. Mawar 1',
        'kurir'             => 'JNE',
        'metode_pembayaran' => 'Transfer Bank BCA',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([RoleSeeder::class, SizeSeeder::class]);
        DB::table('categories')->insert(['id_category' => 1, 'nama_category' => 'Kaos']);
    }

    private function makeProduct(array $stokPerSize, float $harga = 100000): Product
    {
        $product = Product::create(['nama_product' => 'Kaos '.uniqid(), 'deskripsi' => 'x', 'harga' => $harga]);
        foreach ($stokPerSize as $idSize => $stok) {
            $product->allVariants()->create(['id_size' => $idSize, 'stok' => $stok]);
        }

        return $product->fresh();
    }

    private function addToCart(User $user, ProductVariant $variant, int $qty): CartItem
    {
        $cart = Cart::firstOrCreate(['id_user' => $user->id_user]);

        return CartItem::create(['id_cart' => $cart->id_cart, 'id_variant' => $variant->id_variant, 'qty' => $qty]);
    }

    private function admin(): User
    {
        return User::factory()->create(['id_role' => 1]);
    }

    private function editPayload(Product $product, array $stok): array
    {
        return [
            'nama_product' => $product->nama_product,
            'id_category'  => 1,
            'deskripsi'    => 'baru',
            'harga'        => 120000,
        ] + $stok;
    }

    public function test_edit_produk_tidak_menghapus_riwayat_pesanan(): void
    {
        $product = $this->makeProduct([1 => 10, 2 => 10]);
        $user = User::factory()->create();
        $variantS = $product->variants()->where('id_size', 1)->first();
        $item = $this->addToCart($user, $variantS, 2);

        $this->actingAs($user)->post('/checkout', self::CHECKOUT_FORM + ['items' => [$item->id_cart_item]])
            ->assertRedirect();
        $this->assertSame(1, OrderItem::count());

        // Admin mengubah stok M dan menghapus ukuran S dari form
        $this->actingAs($this->admin())
            ->put("/admin/products/{$product->id_product}", $this->editPayload($product, [
                'is_multi_size' => 1,
                'stok_ukuran'   => [2 => 5],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, OrderItem::count(), 'Item pesanan lama tidak boleh hilang');
        $this->assertFalse($variantS->fresh()->is_active);
        $this->assertSame(0, $variantS->fresh()->stok);
        $this->assertSame([2], $product->fresh()->variants->pluck('id_size')->all());
        $this->assertSame(5, $product->fresh()->variants->first()->stok);
    }

    public function test_edit_produk_mempertahankan_id_varian_sehingga_keranjang_tetap_ada(): void
    {
        $product = $this->makeProduct([1 => 10]);
        $variant = $product->variants->first();
        $this->addToCart(User::factory()->create(), $variant, 1);

        $this->actingAs($this->admin())
            ->put("/admin/products/{$product->id_product}", $this->editPayload($product, [
                'is_multi_size' => 1,
                'stok_ukuran'   => [1 => 7],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(7, $variant->fresh()->stok);
        $this->assertSame(1, CartItem::count());
    }

    public function test_edit_produk_multi_ukuran_mempertahankan_semua_ukuran(): void
    {
        $product = $this->makeProduct([1 => 10, 2 => 10, 3 => 10, 4 => 10, 5 => 10]);
        $idsBefore = $product->variants->pluck('id_variant', 'id_size')->all();

        $this->actingAs($this->admin())
            ->put("/admin/products/{$product->id_product}", $this->editPayload($product, [
                'is_multi_size' => 1,
                'stok_ukuran'   => ['1' => '5', '2' => '6', '3' => '', '4' => '8', '5' => '9'],
            ]))
            ->assertSessionHasNoErrors();

        $after = $product->fresh()->variants->pluck('stok', 'id_size')->all();
        $this->assertSame([1 => 5, 2 => 6, 4 => 8, 5 => 9], $after);

        // id varian tetap sama (tidak dihapus lalu dibuat ulang)
        $this->assertSame(
            array_intersect_key($idsBefore, $after),
            $product->fresh()->variants->pluck('id_variant', 'id_size')->all()
        );
    }

    public function test_produk_dengan_riwayat_pesanan_tidak_bisa_dihapus(): void
    {
        $product = $this->makeProduct([1 => 10]);
        $user = User::factory()->create();
        $item = $this->addToCart($user, $product->variants->first(), 1);
        $this->actingAs($user)->post('/checkout', self::CHECKOUT_FORM + ['items' => [$item->id_cart_item]]);

        $this->actingAs($this->admin())->delete("/admin/products/{$product->id_product}")
            ->assertSessionHas('error');

        $this->assertNotNull($product->fresh());
        $this->assertSame(1, OrderItem::count());
    }

    public function test_produk_tanpa_riwayat_pesanan_tetap_bisa_dihapus(): void
    {
        $product = $this->makeProduct([1 => 10]);

        $this->actingAs($this->admin())->delete("/admin/products/{$product->id_product}")
            ->assertSessionHas('success');

        $this->assertNull($product->fresh());
    }

    public function test_produk_single_size_disimpan_sebagai_all_size(): void
    {
        $product = $this->makeProduct([1 => 3]);

        $this->actingAs($this->admin())
            ->put("/admin/products/{$product->id_product}", $this->editPayload($product, ['stok_tunggal' => 9]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin())->get('/admin/products')
            ->assertOk()
            ->assertSee($product->nama_product);

        $variants = $product->fresh()->variants;
        $this->assertCount(1, $variants);
        $this->assertSame(Size::allSizeId(), $variants->first()->id_size);
        $this->assertSame(9, $variants->first()->stok);
    }

    public function test_checkout_tidak_bisa_menghapus_keranjang_user_lain(): void
    {
        $product = $this->makeProduct([1 => 10, 2 => 10]);
        $penyerang = User::factory()->create();
        $korban = User::factory()->create();

        $itemPenyerang = $this->addToCart($penyerang, $product->variants[0], 1);
        $itemKorban = $this->addToCart($korban, $product->variants[1], 1);

        $this->actingAs($penyerang)->post('/checkout', self::CHECKOUT_FORM + [
            'items' => [$itemPenyerang->id_cart_item, $itemKorban->id_cart_item],
        ])->assertRedirect();

        $this->assertNotNull($itemKorban->fresh(), 'Item keranjang user lain tidak boleh terhapus');
        $this->assertNull($itemPenyerang->fresh());
        $this->assertSame(1, OrderItem::count());
    }

    public function test_checkout_menolak_jika_stok_tidak_cukup(): void
    {
        $product = $this->makeProduct([1 => 1]);
        $user = User::factory()->create();
        $item = $this->addToCart($user, $product->variants->first(), 5);

        $this->actingAs($user)->post('/checkout', self::CHECKOUT_FORM + ['items' => [$item->id_cart_item]])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->variants->first()->fresh()->stok);
    }

    public function test_status_pesanan_web_dan_api_sama(): void
    {
        $product = $this->makeProduct([1 => 10]);
        $variant = $product->variants->first();
        $user = User::factory()->create();

        $item = $this->addToCart($user, $variant, 1);
        $this->actingAs($user)->post('/checkout', self::CHECKOUT_FORM + ['items' => [$item->id_cart_item]]);

        $this->actingAs($user, 'sanctum')->postJson('/api/orders/direct-buy', [
            'nama_penerima' => 'Budi', 'alamat_pengiriman' => 'Jl. A', 'no_hp' => '0812',
            'kurir' => 'JNE', 'metode_pembayaran' => 'Transfer Bank BCA',
            'id_variant' => $variant->id_variant, 'qty' => 1,
        ])->assertCreated()->assertJsonPath('order.status', Order::STATUS_MENUNGGU_PEMBAYARAN);

        $this->assertSame(
            [Order::STATUS_MENUNGGU_PEMBAYARAN],
            Order::pluck('status_pesanan')->unique()->values()->all()
        );
        $this->assertSame(8, $variant->fresh()->stok);
    }

    public function test_api_checkout_keranjang_menolak_jika_stok_tidak_cukup(): void
    {
        $product = $this->makeProduct([1 => 1]);
        $user = User::factory()->create();
        $this->addToCart($user, $product->variants->first(), 3);

        $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
            'nama_penerima' => 'Budi', 'alamat_pengiriman' => 'Jl. A', 'no_hp' => '0812',
            'kurir' => 'JNE', 'metode_pembayaran' => 'Transfer Bank BCA',
        ])->assertStatus(400);

        $this->assertSame(1, $product->variants->first()->fresh()->stok);
        $this->assertSame(0, Order::count());
    }

    public function test_admin_bisa_set_status_dibatalkan_dan_menolak_status_asing(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'id_user' => $user->id_user, 'tanggal_order' => now(), 'alamat_pengiriman' => 'x',
            'kurir' => 'JNE', 'total_harga' => 1, 'status_pesanan' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/orders/{$order->id_order}/status", ['status_pesanan' => 'sudah dibayar'])
            ->assertSessionHasErrors('status_pesanan');

        $this->actingAs($admin)->put("/admin/orders/{$order->id_order}/status", ['status_pesanan' => Order::STATUS_DIBATALKAN])
            ->assertSessionHasNoErrors();
        $this->assertSame(Order::STATUS_DIBATALKAN, $order->fresh()->status_pesanan);

        $this->actingAs($admin)->get('/admin/orders?status=dibatalkan')->assertOk()->assertSee('Dibatalkan');
    }

    public function test_tambah_ke_keranjang_dibatasi_stok(): void
    {
        $product = $this->makeProduct([1 => 3]);
        $user = User::factory()->create();

        $this->actingAs($user)->post("/cart/add/{$product->id_product}", ['size' => 'S', 'quantity' => 2]);
        $this->actingAs($user)->post("/cart/add/{$product->id_product}", ['size' => 'S', 'quantity' => 2])
            ->assertSessionHas('success');

        $this->assertSame(3, CartItem::first()->qty);
    }

    public function test_ubah_jumlah_di_keranjang(): void
    {
        $product = $this->makeProduct([1 => 4]);
        $user = User::factory()->create();
        $item = $this->addToCart($user, $product->variants->first(), 1);

        $this->actingAs($user)->patchJson("/cart/update/{$item->id_cart_item}", ['qty' => 3])
            ->assertOk()->assertJson(['qty' => 3]);
        $this->assertSame(3, $item->fresh()->qty);

        $this->actingAs($user)->patchJson("/cart/update/{$item->id_cart_item}", ['qty' => 9])
            ->assertStatus(422);

        // Item milik orang lain tidak bisa diubah
        $this->actingAs(User::factory()->create())->patchJson("/cart/update/{$item->id_cart_item}", ['qty' => 2])
            ->assertNotFound();
    }

    public function test_tambah_ke_keranjang_dengan_ukuran_tidak_ada_tidak_error_500(): void
    {
        $product = $this->makeProduct([1 => 10]);

        $this->actingAs(User::factory()->create())
            ->post("/cart/add/{$product->id_product}", ['size' => 'XXL', 'quantity' => 1])
            ->assertRedirect()
            ->assertSessionHasErrors('size');
    }
}
