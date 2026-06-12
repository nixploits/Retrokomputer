<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola retur.'], 403);
        }

        $returs = Retur::with(['user:id,name', 'details.produk', 'pembelian:id,supplier'])->orderBy('created_at', 'desc')->get();
        return response()->json($returs);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengelola retur.'], 403);
        }

        $validated = $request->validate([
            'jenis_retur' => 'required|in:penjualan,pembelian',
            'referensi_id' => 'required|integer',
            'alasan' => 'required|string',
            'ongkir' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $retur = Retur::create([
                'user_id' => $request->user()->id,
                'jenis_retur' => $validated['jenis_retur'],
                'referensi_id' => $validated['referensi_id'],
                'alasan' => $validated['alasan'],
                'ongkir' => $validated['ongkir'] ?? 0
            ]);

            foreach ($validated['items'] as $item) {
                // Create detail
                $retur->details()->create([
                    'produk_id' => $item['produk_id'],
                    'qty' => $item['qty']
                ]);

                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

                if ($validated['jenis_retur'] == 'penjualan') {
                    // Retur dari customer (barang kembali ke toko/rusak)
                    // Asumsi barang dikembalikan dan dihitung sbg stok, ATAU tidak. Kita kembalikan ke stok
                    $produk->increment('stok', $item['qty']);
                    
                    $produk->riwayatStok()->create([
                        'tipe' => 'masuk',
                        'qty' => $item['qty'],
                        'sumber' => 'Retur Penjualan',
                        'referensi_id' => $retur->id
                    ]);
                } else {
                    // Retur ke supplier (barang keluar dari toko)
                    if ($produk->stok < $item['qty']) {
                        throw new \Exception("Stok tidak cukup untuk produk: " . $produk->nama_produk);
                    }
                    $produk->decrement('stok', $item['qty']);
                    
                    $produk->riwayatStok()->create([
                        'tipe' => 'keluar',
                        'qty' => $item['qty'],
                        'sumber' => 'Retur Pembelian',
                        'referensi_id' => $retur->id
                    ]);
                }
            }

            DB::commit();

            return response()->json($retur->load('details.produk'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
