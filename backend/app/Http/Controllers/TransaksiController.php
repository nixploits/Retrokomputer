<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with('kasir:id,name')->orderBy('created_at', 'desc');

        // Kasir only sees own transactions
        if ($request->user()->role === 'kasir') {
            $query->where('user_id', $request->user()->id);
        }

        // Apply date filters if provided
        $filterMode = $request->query('filter_mode');
        $filterValue = $request->query('filter_value');
        $filterYear = $request->query('filter_year', Carbon::now()->year);

        if ($filterMode) {
            $this->applyDateFilter($query, $filterMode, $filterValue, $filterYear);
        }

        $transaksis = $query->get();
        return response()->json($transaksis);
    }

    /**
     * Apply date filter to a query based on filter_mode and filter_value.
     *
     * Modes:
     * - harian   : filter by day-of-week within the current month/year (filter_year is ignored)
     * - mingguan : filter by week number (1-5) within the current month/year (filter_year is ignored)
     * - bulanan  : filter by month (1-12) within filter_year
     * - tanggal  : filter by exact date (YYYY-MM-DD)
     * - default  : current month and year
     */
    private function applyDateFilter($query, $filterMode, $filterValue, $filterYear)
    {
        $now = Carbon::now();

        switch ($filterMode) {
            case 'hari_ini':
                $query->whereDate('created_at', $now->toDateString());
                break;

            case 'minggu_ini':
                $query->whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek(),
                ]);
                break;

            case 'bulan_ini':
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;

            case 'tahunan':
                $year = intval($filterValue ?: $now->year);
                if ($year < 2000 || $year > 2100) $year = $now->year;
                $query->whereYear('created_at', $year);
                break;

            case 'harian':
                $dayMap = [
                    'senin' => Carbon::MONDAY,
                    'selasa' => Carbon::TUESDAY,
                    'rabu' => Carbon::WEDNESDAY,
                    'kamis' => Carbon::THURSDAY,
                    'jumat' => Carbon::FRIDAY,
                    'sabtu' => Carbon::SATURDAY,
                    'minggu' => Carbon::SUNDAY,
                ];
                $dayOfWeek = $dayMap[strtolower($filterValue ?? '')] ?? null;
                if ($dayOfWeek !== null) {
                    $query->whereRaw('DAYOFWEEK(created_at) = ?', [$dayOfWeek % 7 + 1]);
                }
                // Always scope to current month + year for consistency
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;

            case 'mingguan':
                $weekNum = intval($filterValue ?? 1);
                if ($weekNum < 1) $weekNum = 1;
                if ($weekNum > 5) $weekNum = 5;

                // Always use current month + year
                $startOfMonth = $now->copy()->startOfMonth();
                $weekStart = $startOfMonth->copy()->addWeeks($weekNum - 1);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

                if ($weekStart->lt($startOfMonth)) {
                    $weekStart = $startOfMonth->copy();
                }
                $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
                if ($weekEnd->gt($endOfMonth)) {
                    $weekEnd = $endOfMonth->copy();
                }

                $query->whereBetween('created_at', [$weekStart, $weekEnd]);
                break;

            case 'bulanan':
                $month = intval($filterValue ?? $now->month);
                if ($month < 1 || $month > 12) {
                    $month = $now->month;
                }
                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $filterYear);
                break;

            case 'tanggal':
                if ($filterValue) {
                    try {
                        $date = Carbon::parse($filterValue);
                        $query->whereDate('created_at', $date->toDateString());
                    } catch (\Exception $e) {
                        $query->whereDate('created_at', Carbon::today()->toDateString());
                    }
                } else {
                    $query->whereDate('created_at', Carbon::today()->toDateString());
                }
                break;

            case 'rentang':
                // filter_value = "YYYY-MM-DD,YYYY-MM-DD" (tanggal awal, tanggal akhir)
                $parts = explode(',', (string) $filterValue);
                if (count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '') {
                    try {
                        $start = Carbon::parse(trim($parts[0]))->startOfDay();
                        $end = Carbon::parse(trim($parts[1]))->endOfDay();
                        // Tukar jika urutan terbalik
                        if ($start->gt($end)) {
                            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                        }
                        $query->whereBetween('created_at', [$start, $end]);
                    } catch (\Exception $e) {
                        $query->whereDate('created_at', Carbon::today()->toDateString());
                    }
                } else {
                    $query->whereDate('created_at', Carbon::today()->toDateString());
                }
                break;

            default:
                // Unknown mode → fallback to current month
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;
        }

        return $query;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'metode_pembayaran' => 'required|in:tunai,debit,transfer',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.qty' => 'required|integer|min:1',
            'nama_pembeli' => 'nullable|string|max:255'
        ]);

        $activeProfile = \App\Models\ProfilKasir::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (!$activeProfile) {
            return response()->json([
                'message' => 'Profil kasir aktif tidak ditemukan. Silakan pilih dan aktifkan profil kasir terlebih dahulu.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $total = 0;
            $kode = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);

            $transaksi = Transaksi::create([
                'user_id' => $request->user()->id,
                'kode_transaksi' => $kode,
                'total' => 0,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'nama_kasir' => $activeProfile->nama,
                'nama_pembeli' => $validated['nama_pembeli'] ?? null
            ]);

            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                
                if ($produk->stok < $item['qty']) {
                    throw new \Exception("Stok tidak cukup untuk produk: " . $produk->nama_produk);
                }

                $subtotal = $produk->harga_jual * $item['qty'];
                $total += $subtotal;

                // Create detail
                $transaksi->details()->create([
                    'produk_id' => $produk->id,
                    'qty' => $item['qty'],
                    'harga_satuan' => $produk->harga_jual,
                    'subtotal' => $subtotal
                ]);

                // Reduce stock
                $produk->decrement('stok', $item['qty']);

                // Record history
                $produk->riwayatStok()->create([
                    'tipe' => 'keluar',
                    'qty' => $item['qty'],
                    'sumber' => 'Penjualan',
                    'referensi_id' => $transaksi->id
                ]);
            }

            $transaksi->update(['total' => $total]);

            DB::commit();

            return response()->json($transaksi->load('details.produk'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, $id)
    {
        $transaksi = Transaksi::with(['details.produk', 'kasir:id,name'])->findOrFail($id);

        // Kasir hanya boleh melihat transaksi miliknya sendiri
        if ($request->user()->role === 'kasir' && $transaksi->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak berhak melihat transaksi ini.'], 403);
        }

        return response()->json($transaksi);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat menghapus transaksi.'], 403);
        }

        DB::beginTransaction();

        try {
            $transaksi = Transaksi::with('details')->findOrFail($id);

            foreach ($transaksi->details as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    // Restore stock
                    $produk->increment('stok', $detail->qty);

                    // Record to stok history as cancellation
                    $produk->riwayatStok()->create([
                        'tipe' => 'masuk',
                        'qty' => $detail->qty,
                        'sumber' => 'Pembatalan Transaksi oleh Admin',
                        'referensi_id' => $transaksi->id
                    ]);
                }
            }

            // Delete transaction (cascades to details)
            $transaksi->delete();

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil dihapus, stok dan keuangan telah dikembalikan.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
