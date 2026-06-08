<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['images', 'variants'])
            ->when($search, function ($query, $search) {
                return $query->where('nama_product', 'like', '%' . $search . '%');
            })
            ->paginate(20)
            ->withQueryString();

        return view('admin.katalog', compact('products', 'search'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_product' => 'required|string|max:255|unique:products,nama_product',
            'deskripsi'    => 'required|string',
            'harga'        => 'required|numeric|min:0|max:9999999999999',
            'foto_utama'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_tambahan.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Validasi stok: jika multi size, minimal 1 ukuran harus diisi
        // Jika single, stok_tunggal wajib diisi
        if ($request->has('is_multi_size')) {
            $rules['stok_ukuran'] = 'required|array|min:1';
            $rules['stok_ukuran.*'] = 'nullable|integer|min:0';
        } else {
            $rules['stok_tunggal'] = 'required|integer|min:0';
        }

        $messages = [
            'nama_product.required'  => '⚠️ Nama produk wajib diisi.',
            'nama_product.unique'    => '⚠️ Nama produk "' . $request->nama_product . '" sudah digunakan, pilih nama lain.',
            'nama_product.max'       => '⚠️ Nama produk maksimal 255 karakter.',
            'deskripsi.required'     => '⚠️ Deskripsi produk wajib diisi.',
            'harga.required'         => '⚠️ Harga produk wajib diisi.',
            'harga.numeric'          => '⚠️ Harga harus berupa angka.',
            'harga.min'              => '⚠️ Harga tidak boleh negatif.',
            'harga.max'              => '⚠️ Harga terlalu besar! Maksimal harga adalah Rp 9.999.999.999.999.',
            'foto_utama.required'    => '📷 Foto utama wajib diunggah. Silakan pilih gambar produk.',
            'foto_utama.image'       => '📷 File foto utama harus berupa gambar (jpg, png, jpeg).',
            'foto_utama.mimes'       => '📷 Foto utama hanya boleh berformat: jpeg, png, jpg.',
            'foto_utama.max'         => '📷 Ukuran foto utama maksimal 2MB.',
            'foto_tambahan.*.image'  => '📷 Salah satu foto tambahan bukan file gambar yang valid.',
            'foto_tambahan.*.mimes'  => '📷 Foto tambahan hanya boleh berformat: jpeg, png, jpg.',
            'foto_tambahan.*.max'    => '📷 Ukuran setiap foto tambahan maksimal 2MB.',
            'stok_tunggal.required'  => '📦 Stok produk wajib diisi. Masukkan jumlah stok tersedia.',
            'stok_tunggal.integer'   => '📦 Stok harus berupa angka bulat.',
            'stok_tunggal.min'       => '📦 Stok tidak boleh negatif.',
            'stok_ukuran.required'   => '📦 Minimal isi stok untuk satu ukuran.',
            'stok_ukuran.*.integer'  => '📦 Stok per ukuran harus berupa angka bulat.',
            'stok_ukuran.*.min'      => '📦 Stok per ukuran tidak boleh negatif.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        // Validasi tambahan: jika multi size, minimal 1 stok harus diisi (bukan semua null)
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
                    $validator->errors()->add('stok_ukuran', '📦 Minimal isi stok untuk setidaknya satu ukuran (S, M, L, atau XL).');
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
            $product = Product::create([
                'nama_product' => $request->nama_product,
                'deskripsi'    => $request->deskripsi,
                'harga'        => $request->harga,
            ]);

            $pathUtama = $request->file('foto_utama')->store('products', 'public');
            ProductImage::create([
                'id_product' => $product->id_product,
                'url_gambar' => $pathUtama,
                'is_primary' => 1
            ]);

            if ($request->hasFile('foto_tambahan')) {
                foreach ($request->file('foto_tambahan') as $file) {
                    $pathTambahan = $file->store('products', 'public');
                    ProductImage::create([
                        'id_product' => $product->id_product,
                        'url_gambar' => $pathTambahan,
                        'is_primary' => 0
                    ]);
                }
            }

            if ($request->has('is_multi_size')) {
                foreach ($request->stok_ukuran as $id_size => $stok) {
                    if ($stok !== null && $stok !== '') {
                        DB::table('product_variants')->insert([
                            'id_product' => $product->id_product,
                            'id_size'    => $id_size,
                            'stok'       => $stok
                        ]);
                    }
                }
            } else {
                DB::table('product_variants')->insert([
                    'id_product' => $product->id_product,
                    'id_size'    => 1, 
                    'stok'       => $request->stok_tunggal
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['store_error' => '❌ Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_product' => 'required|string|max:255|unique:products,nama_product,' . $id . ',id_product',
            'deskripsi'    => 'required|string',
            'harga'        => 'required|numeric',
        ], [
            'nama_product.unique' => 'Nama produk ini sudah digunakan oleh produk lain.'
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'nama_product' => $request->nama_product,
            'deskripsi'    => $request->deskripsi,
            'harga'        => $request->harga,
        ]);

        if ($request->hasFile('foto_utama')) {
            $oldUtama = ProductImage::where('id_product', $id)->where('is_primary', 1)->first();
            if ($oldUtama) {
                Storage::disk('public')->delete($oldUtama->url_gambar);
                $oldUtama->delete();
            }
            ProductImage::create([
                'id_product' => $id,
                'url_gambar' => $request->file('foto_utama')->store('products', 'public'),
                'is_primary' => 1
            ]);
        }

        if ($request->hasFile('foto_tambahan')) {
            $oldTambahan = ProductImage::where('id_product', $id)->where('is_primary', 0)->get();
            foreach ($oldTambahan as $ot) {
                Storage::disk('public')->delete($ot->url_gambar);
                $ot->delete();
            }
            foreach ($request->file('foto_tambahan') as $file) {
                ProductImage::create([
                    'id_product' => $id,
                    'url_gambar' => $file->store('products', 'public'),
                    'is_primary' => 0
                ]);
            }
        }

        DB::table('product_variants')->where('id_product', $id)->delete();
        
        if ($request->has('is_multi_size')) {
            foreach ($request->stok_ukuran as $id_size => $stok) {
                if ($stok !== null) {
                    DB::table('product_variants')->insert([
                        'id_product' => $id,
                        'id_size'    => $id_size,
                        'stok'       => $stok
                    ]);
                }
            }
        } else {
            DB::table('product_variants')->insert([
                'id_product' => $id,
                'id_size'    => 1, 
                'stok'       => $request->stok_tunggal
            ]);
        }

        return redirect()->back()->with('success', 'Data produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->url_gambar);
        }
        
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }
}