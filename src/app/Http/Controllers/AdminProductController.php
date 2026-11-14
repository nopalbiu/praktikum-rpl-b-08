<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary; // Memanggil library Cloudinary

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Mengambil semua data kategori untuk ditampilkan di dropdown modal
        $categories = DB::table('categories')->get();

        // Menambahkan 'categories' ke dalam relasi yang dipanggil
        $products = Product::with(['images', 'variants', 'categories'])
            ->when($search, function ($query, $search) {
                return $query->where('nama_product', 'like', '%' . $search . '%');
            })
            ->paginate(20)
            ->withQueryString();

        // Mengirimkan $categories ke view
        $allSizeId = Size::allSizeId();
        $sizes = Size::where('id_size', '!=', $allSizeId)->orderBy('id_size')->get();

        return view('admin.products.index', compact('products', 'search', 'categories', 'allSizeId', 'sizes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_product' => 'required|string|max:255|unique:products,nama_product',
            'id_category'  => 'required', // Validasi kategori wajib diisi
            'deskripsi'    => 'required|string',
            'material_pakaian' => 'nullable|string|max:150',
            'harga'        => 'required|numeric|min:0|max:9999999999999',
            'foto_utama'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_tambahan.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($request->has('is_multi_size')) {
            $rules['stok_ukuran'] = 'required|array|min:1';
            $rules['stok_ukuran.*'] = 'nullable|integer|min:0';
        } else {
            $rules['stok_tunggal'] = 'required|integer|min:0';
        }

        $messages = [
            'nama_product.required'  => 'Nama produk wajib diisi.',
            'nama_product.unique'    => 'Nama produk "' . $request->nama_product . '" sudah digunakan, pilih nama lain.',
            'nama_product.max'       => 'Nama produk maksimal 255 karakter.',
            'id_category.required'   => 'Kategori produk wajib dipilih.',
            'deskripsi.required'     => 'Deskripsi produk wajib diisi.',
            'harga.required'         => 'Harga produk wajib diisi.',
            'harga.numeric'          => 'Harga harus berupa angka.',
            'harga.min'              => 'Harga tidak boleh negatif.',
            'harga.max'              => 'Harga terlalu besar! Maksimal harga adalah Rp 9.999.999.999.999.',
            'foto_utama.required'    => 'Foto utama wajib diunggah. Silakan pilih gambar produk.',
            'foto_utama.image'       => 'File foto utama harus berupa gambar (jpg, png, jpeg).',
            'foto_utama.mimes'       => 'Foto utama hanya boleh berformat: jpeg, png, jpg.',
            'foto_utama.max'         => 'Ukuran foto utama maksimal 2MB.',
            'foto_tambahan.*.image'  => 'Salah satu foto tambahan bukan file gambar yang valid.',
            'foto_tambahan.*.mimes'  => 'Foto tambahan hanya boleh berformat: jpeg, png, jpg.',
            'foto_tambahan.*.max'    => 'Ukuran setiap foto tambahan maksimal 2MB.',
            'stok_tunggal.required'  => 'Stok produk wajib diisi. Masukkan jumlah stok tersedia.',
            'stok_tunggal.integer'   => 'Stok harus berupa angka bulat.',
            'stok_tunggal.min'       => 'Stok tidak boleh negatif.',
            'stok_ukuran.required'   => 'Minimal isi stok untuk satu ukuran.',
            'stok_ukuran.*.integer'  => 'Stok per ukuran harus berupa angka bulat.',
            'stok_ukuran.*.min'      => 'Stok per ukuran tidak boleh negatif.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($request->has('is_multi_size')) {
            $validator->after(function ($validator) use ($request) {
                $stokUkuran = $request->input('stok_ukuran', []);
                $adaStok = false;
                foreach ($stokUkuran as $stok) {
                    if ($stok !== null && $stok !== '') {
                        $adaStok = true;
                        break;
                    }
                }
                if (!$adaStok) {
                    // Update pesan error agar menyertakan XXL
                    $validator->errors()->add('stok_ukuran', 'Minimal isi stok untuk setidaknya satu ukuran (S, M, L, XL, atau XXL).');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Data Produk Utama
            $product = Product::create([
                'nama_product'     => $request->nama_product,
                'deskripsi'        => $request->deskripsi,
                'material_pakaian' => $request->material_pakaian,
                'harga'            => $request->harga,
            ]);

            // 2. Simpan Relasi Kategori ke tabel product_category
            DB::table('product_category')->insert([
                'id_product'  => $product->id_product,
                'id_category' => $request->id_category,
            ]);

            // ==========================================
            // LOGIKA UPLOAD CLOUDINARY (STORE)
            // ==========================================
            $cloudinary = new Cloudinary(config('services.cloudinary.url'));

            // Upload Foto Utama
            $uploadUtama = $cloudinary->uploadApi()->upload($request->file('foto_utama')->getRealPath(), [
                'folder' => 'wearworeworn_products'
            ]);
            
            ProductImage::create([
                'id_product' => $product->id_product,
                'url_gambar' => $uploadUtama['secure_url'],
                'is_primary' => 1
            ]);

            // Upload Foto Tambahan (Jika Ada)
            if ($request->hasFile('foto_tambahan')) {
                foreach ($request->file('foto_tambahan') as $file) {
                    $uploadTambahan = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'wearworeworn_products'
                    ]);
                    
                    ProductImage::create([
                        'id_product' => $product->id_product,
                        'url_gambar' => $uploadTambahan['secure_url'],
                        'is_primary' => 0
                    ]);
                }
            }
            // ==========================================

            // 3. Simpan Varian Stok
            $this->syncVariants($product, $request);

            DB::commit();
            return redirect()->back()->with('success', "Produk \"{$product->nama_product}\" ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()
                ->withInput()
                ->withErrors(['store_error' => 'Produk gagal disimpan karena kesalahan sistem (misalnya upload foto gagal). Coba lagi.']);
        }
    }

    public function update(Request $request, $id)
    {
        $stokRules = $request->has('is_multi_size')
            ? ['stok_ukuran' => 'required|array|min:1', 'stok_ukuran.*' => 'nullable|integer|min:0']
            : ['stok_tunggal' => 'required|integer|min:0'];

        $request->validate([
            'nama_product' => 'required|string|max:255|unique:products,nama_product,' . $id . ',id_product',
            'id_category'  => 'required', // Validasi kategori
            'deskripsi'    => 'required|string',
            'material_pakaian' => 'nullable|string|max:150',
            'harga'        => 'required|numeric|min:0|max:9999999999999',
            'foto_utama'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_tambahan.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ] + $stokRules, [
            'nama_product.required' => 'Nama produk wajib diisi.',
            'nama_product.unique' => 'Nama produk ini sudah dipakai produk lain.',
            'id_category.required' => 'Kategori produk wajib dipilih.',
            'deskripsi.required' => 'Deskripsi produk wajib diisi.',
            'harga.required' => 'Harga produk wajib diisi.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'foto_utama.image' => 'Foto utama harus berupa gambar (jpg atau png).',
            'foto_utama.max' => 'Ukuran foto utama maksimal 2MB.',
            'foto_tambahan.*.max' => 'Ukuran setiap foto tambahan maksimal 2MB.',
            'stok_tunggal.required' => 'Stok wajib diisi.',
            'stok_tunggal.min' => 'Stok tidak boleh negatif.',
            'stok_ukuran.*.min' => 'Stok per ukuran tidak boleh negatif.',
        ]);

        $product = Product::findOrFail($id);

        // 1. Update Data Produk Utama
        $product->update([
            'nama_product'     => $request->nama_product,
            'deskripsi'        => $request->deskripsi,
            'material_pakaian' => $request->material_pakaian,
            'harga'            => $request->harga,
        ]);

        // 2. Update Relasi Kategori (Gunakan updateOrInsert agar aman jika belum ada sebelumnya)
        DB::table('product_category')->updateOrInsert(
            ['id_product' => $id],
            ['id_category' => $request->id_category]
        );

        // ==========================================
        // LOGIKA UPLOAD CLOUDINARY (UPDATE)
        // ==========================================
        $cloudinary = new Cloudinary(config('services.cloudinary.url'));

        if ($request->hasFile('foto_utama')) {
            $oldUtama = ProductImage::where('id_product', $id)->where('is_primary', 1)->first();
            if ($oldUtama) {
                $oldUtama->delete();
            }
            
            $uploadUtama = $cloudinary->uploadApi()->upload($request->file('foto_utama')->getRealPath(), [
                'folder' => 'wearworeworn_products'
            ]);

            ProductImage::create([
                'id_product' => $id,
                'url_gambar' => $uploadUtama['secure_url'],
                'is_primary' => 1
            ]);
        }

        if ($request->hasFile('foto_tambahan')) {
            $oldTambahan = ProductImage::where('id_product', $id)->where('is_primary', 0)->get();
            foreach ($oldTambahan as $ot) {
                $ot->delete(); 
            }
            
            foreach ($request->file('foto_tambahan') as $file) {
                $uploadTambahan = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'wearworeworn_products'
                ]);

                ProductImage::create([
                    'id_product' => $id,
                    'url_gambar' => $uploadTambahan['secure_url'],
                    'is_primary' => 0
                ]);
            }
        }
        // ==========================================

        // 3. Update Varian Stok (tanpa menghapus varian yang sudah ada di riwayat pesanan)
        DB::transaction(fn () => $this->syncVariants($product, $request));

        return redirect()->back()->with('success', "Perubahan \"{$product->nama_product}\" disimpan.");
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->hasOrderHistory()) {
            return redirect()->back()->with('error',
                'Produk "' . $product->nama_product . '" sudah ada di riwayat pesanan sehingga tidak bisa dihapus. '
                . 'Kosongkan stoknya agar produk tidak bisa dibeli lagi.');
        }
        
        // Hapus relasi kategori terlebih dahulu sebelum produk dihapus
        DB::table('product_category')->where('id_product', $id)->delete();
        
        $product->delete();

        return redirect()->back()->with('success', "Produk \"{$product->nama_product}\" dihapus.");
    }
    /**
     * Menyamakan varian produk dengan input form.
     * Varian yang sudah ada di-update (id_variant tetap), yang baru dibuat, dan yang
     * dihapus dari form: dinonaktifkan jika sudah dipakai di pesanan, selain itu dihapus.
     */
    private function syncVariants(Product $product, Request $request): void
    {
        if ($request->has('is_multi_size')) {
            $desired = collect($request->input('stok_ukuran', []))
                ->filter(fn ($stok) => $stok !== null && $stok !== '')
                ->map(fn ($stok) => (int) $stok);
        } else {
            $desired = collect([Size::allSizeId() => (int) $request->input('stok_tunggal')]);
        }

        $existing = $product->allVariants()->get()->keyBy(fn ($v) => (int) $v->id_size);
        $desired = $desired->mapWithKeys(fn ($stok, $idSize) => [(int) $idSize => $stok]);

        foreach ($desired as $idSize => $stok) {
            if ($variant = $existing->get($idSize)) {
                $variant->update(['stok' => $stok, 'is_active' => true]);
            } else {
                $product->allVariants()->create([
                    'id_size'   => $idSize,
                    'stok'      => $stok,
                    'is_active' => true,
                ]);
            }
        }

        // Varian yang tidak ada lagi di form (dibandingkan eksplisit per id_size)
        $removed = $existing->reject(fn ($variant, $idSize) => array_key_exists($idSize, $desired->all()));
        foreach ($removed as $variant) {
            $dipakaiDiPesanan = DB::table('order_items')->where('id_variant', $variant->id_variant)->exists();

            if ($dipakaiDiPesanan) {
                $variant->update(['stok' => 0, 'is_active' => false]);
                $variant->cartItems()->delete();
            } else {
                $variant->delete();
            }
        }
    }
}