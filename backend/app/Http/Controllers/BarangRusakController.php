<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangRusak;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;

class BarangRusakController extends Controller
{
    /**
     * Get list of recorded lost/damaged goods
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya admin atau owner yang dapat mengakses data barang rusak/hilang.'], 403);
        }

        $records = BarangRusak::with('produk')->orderBy('created_at', 'desc')->get();
        return response()->json($records);
    }

    /**
     * Store new lost/damaged goods record
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya admin atau owner yang dapat mencatat barang rusak/hilang.'], 403);
        }

        // Support form-data type conversions
        if ($request->has('produk_id')) {
            $request->merge([
                'produk_id' => (int) $request->input('produk_id'),
                'qty' => (int) $request->input('qty'),
            ]);
        }

        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'required|integer|min:1',
            'kategori' => 'required|string|in:rusak,hilang',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|image|max:4096',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_file')) {
            $path = $request->file('bukti_file')->store('barang_rusak', 'public');
            $buktiPath = '/storage/' . $path;
        }

        $result = DB::transaction(function () use ($request, $buktiPath) {
            $produk = Produk::lockForUpdate()->findOrFail($request->produk_id);

            if ($produk->stok < $request->qty) {
                throw new \Exception('Stok produk tidak mencukupi untuk dilaporkan.');
            }

            // Decrement product stock
            $produk->decrement('stok', $request->qty);

            // Save damaged goods record
            $barangRusak = BarangRusak::create([
                'produk_id' => $request->produk_id,
                'qty' => $request->qty,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan,
                'bukti_foto' => $buktiPath,
            ]);

            // Save stok history record to reflect in Laba Rugi loss calculations
            RiwayatStok::create([
                'produk_id' => $request->produk_id,
                'tipe' => 'keluar',
                'qty' => $request->qty,
                'sumber' => $request->kategori === 'rusak' ? 'Barang Rusak' : 'Barang Hilang',
            ]);

            return $barangRusak;
        });

        return response()->json([
            'message' => 'Berhasil mencatat barang rusak/hilang',
            'data' => $result->load('produk')
        ], 201);
    }
}
