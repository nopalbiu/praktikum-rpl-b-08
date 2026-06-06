<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'   => 'required',
            'deskripsi'     => 'required',
            'harga'         => 'required|numeric',
            'total_stok'    => 'required|integer',
            'foto_utama'    => 'required|image',
            'foto_sizechart'=> 'nullable|image',
            'foto_tambahan' => 'nullable|array',
        ]);

        $data = $request->only(['nama_produk', 'deskripsi', 'harga', 'total_stok']);

        $data['foto_utama'] = $request->file('foto_utama')->store('products', 'public');
        
        if ($request->hasFile('foto_sizechart')) {
            $data['foto_sizechart'] = $request->file('foto_sizechart')->store('sizecharts', 'public');
        }

        if ($request->hasFile('foto_tambahan')) {
            $paths = [];
            foreach ($request->file('foto_tambahan') as $file) {
                $paths[] = $file->store('products', 'public');
            }
            $data['foto_tambahan'] = json_encode($paths);
        }

        Product::create($data);
        return response()->json(['message' => 'Produk berhasil disimpan'], 201);
    }
}