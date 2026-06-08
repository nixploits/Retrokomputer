<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return response()->json($query->orderBy('nama_produk')->get());
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola produk.'], 403);
        }

        $validated = $request->validate([
            'kode_produk' => 'required|unique:produks',
            'nama_produk' => 'required|string',
            'kategori' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'status' => 'in:aktif,nonaktif'
        ]);

        // Default status to 'aktif' if not provided
        $validated['status'] = $validated['status'] ?? 'aktif';

        $produk = Produk::create($validated);

        if ($produk->stok > 0) {
            $produk->riwayatStok()->create([
                'tipe' => 'masuk',
                'qty' => $produk->stok,
                'sumber' => 'Stok Awal'
            ]);
        }

        return response()->json($produk, 201);
    }

    public function show(Produk $produk)
    {
        return response()->json($produk);
    }

    public function update(Request $request, Produk $produk)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola produk.'], 403);
        }

        $validated = $request->validate([
            'kode_produk' => 'required|unique:produks,kode_produk,' . $produk->id,
            'nama_produk' => 'required|string',
            'kategori' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'status' => 'in:aktif,nonaktif'
        ]);

        $produk->update($validated);

        return response()->json($produk);
    }

    public function destroy(Request $request, Produk $produk)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola produk.'], 403);
        }

        $produk->delete();
        return response()->json(['message' => 'Produk dihapus']);
    }
}
