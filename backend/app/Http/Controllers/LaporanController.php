<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function dashboard(Request $request)
    {
        $role = $request->user()->role;

        // Build date filter based on filter_mode
        $filterMode = $request->query('filter_mode');
        $filterValue = $request->query('filter_value');
        $filterYear = $request->query('filter_year', Carbon::now()->year);

        $penjualanQuery = Transaksi::query();
        $totalTransaksiQuery = Transaksi::query();

        // Apply role-based filter for kasir
        if ($role === 'kasir') {
            $penjualanQuery = $penjualanQuery->where('user_id', $request->user()->id);
            $totalTransaksiQuery = $totalTransaksiQuery->where('user_id', $request->user()->id);
        }

        // Apply date filters
        $this->applyDateFilter($penjualanQuery, $filterMode, $filterValue, $filterYear);
        $this->applyDateFilter($totalTransaksiQuery, $filterMode, $filterValue, $filterYear);

        $penjualanBulanIni = $penjualanQuery->sum('total');
        $totalTransaksi = $totalTransaksiQuery->count();

        // Kasir tidak boleh melihat Pembelian, Laba Bersih, dan Kerugian Inventaris
        if ($role === 'kasir') {
            return response()->json([
                'penjualan_bulan_ini' => $penjualanBulanIni,
                'pembelian_bulan_ini' => 0,
                'laba_bersih' => 0,
                'total_transaksi' => $totalTransaksi,
                'kerugian_inventaris' => 0
            ]);
        }

        $pembelianQuery = Pembelian::query();
        $this->applyDateFilter($pembelianQuery, $filterMode, $filterValue, $filterYear);
        $pembelianBulanIni = $pembelianQuery->sum('total');

        // Riwayat stok rusak/hilang — agregasi tanpa memuat seluruh baris ke memori
        $kerugianQuery = RiwayatStok::whereIn('sumber', ['Barang Rusak', 'Barang Hilang']);
        $this->applyDateFilter($kerugianQuery, $filterMode, $filterValue, $filterYear);
        $kerugianRows = $kerugianQuery
            ->select('produk_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('produk_id')
            ->get();
        $hargaBeliMap = Produk::whereIn('id', $kerugianRows->pluck('produk_id'))
            ->pluck('harga_beli', 'id');
        $kerugianInventaris = $kerugianRows->sum(function ($row) use ($hargaBeliMap) {
            return $row->total_qty * ($hargaBeliMap[$row->produk_id] ?? 0);
        });

        $labaBersih = $penjualanBulanIni - $pembelianBulanIni - $kerugianInventaris;

        return response()->json([
            'penjualan_bulan_ini' => $penjualanBulanIni,
            'pembelian_bulan_ini' => $pembelianBulanIni,
            'laba_bersih' => $labaBersih,
            'total_transaksi' => $totalTransaksi,
            'kerugian_inventaris' => $kerugianInventaris
        ]);
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
                // filter_value = tahun (mis. 2026)
                $year = intval($filterValue ?: $now->year);
                if ($year < 2000 || $year > 2100) $year = $now->year;
                $query->whereYear('created_at', $year);
                break;

            case 'harian':
                // filter_value = day name in Indonesian: senin, selasa, rabu, kamis, jumat, sabtu, minggu
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
                // filter_value = 1, 2, 3, 4, 5 (week number in current month)
                $weekNum = intval($filterValue ?? 1);
                if ($weekNum < 1) $weekNum = 1;
                if ($weekNum > 5) $weekNum = 5;

                // Always use current month + year
                $startOfMonth = $now->copy()->startOfMonth();
                $weekStart = $startOfMonth->copy()->addWeeks($weekNum - 1);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

                // Clamp to month boundaries
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
                // filter_value = 1-12 (month number)
                $month = intval($filterValue ?? $now->month);
                if ($month < 1 || $month > 12) {
                    $month = $now->month;
                }
                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $filterYear);
                break;

            case 'tanggal':
                // filter_value = YYYY-MM-DD (exact date)
                if ($filterValue) {
                    try {
                        $date = Carbon::parse($filterValue);
                        $query->whereDate('created_at', $date->toDateString());
                    } catch (\Exception $e) {
                        // Invalid date, default to today
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
                // Default: current month
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;
        }

        return $query;
    }

    /**
     * Hitung total kerugian inventaris (barang rusak/hilang) untuk satu bulan
     * via agregasi SQL — tidak memuat baris ke memori (aman untuk data besar).
     */
    private function sumKerugianByMonth(int $month, int $year): float
    {
        return (float) DB::table('riwayat_stoks')
            ->join('produks', 'riwayat_stoks.produk_id', '=', 'produks.id')
            ->whereIn('riwayat_stoks.sumber', ['Barang Rusak', 'Barang Hilang'])
            ->whereMonth('riwayat_stoks.created_at', $month)
            ->whereYear('riwayat_stoks.created_at', $year)
            ->sum(DB::raw('riwayat_stoks.qty * produks.harga_beli'));
    }

    public function stok(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya admin atau owner yang dapat melihat laporan stok.'], 403);
        }

        $produks = Produk::all();
        $stokAman = $produks->where('stok', '>', 5)->count();
        $stokRendah = $produks->where('stok', '>', 0)->where('stok', '<=', 5)->count();
        $stokHabis = $produks->where('stok', '<=', 0)->count();

        return response()->json([
            'total_jenis_produk' => $produks->count(),
            'stok_aman' => $stokAman,
            'stok_rendah' => $stokRendah,
            'stok_habis' => $stokHabis
        ]);
    }

    public function riwayatStok(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'owner') {
            return response()->json(['message' => 'Hanya admin atau owner yang dapat melihat riwayat stok.'], 403);
        }

        $query = RiwayatStok::with('produk')->orderBy('created_at', 'desc');

        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        return response()->json($query->get());
    }

    /**
     * Chart: Penjualan harian (30 hari terakhir)
     * GET /api/laporan/chart/penjualan-harian
     */
    public function chartPenjualanHarian(Request $request)
    {
        if (!in_array($request->user()->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $hari = intval($request->query('hari', 30));
        if ($hari <= 0) $hari = 30;

        $startDate = Carbon::now()->subDays($hari - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get actual sales data grouped by date
        $salesData = Transaksi::select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        // Fill in missing dates with zero
        $result = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $result[] = [
                'tanggal' => $dateStr,
                'total' => (float) ($salesData[$dateStr]->total ?? 0),
                'jumlah_transaksi' => (int) ($salesData[$dateStr]->jumlah_transaksi ?? 0),
            ];
            $currentDate->addDay();
        }

        return response()->json($result);
    }

    /**
     * Chart: Penjualan bulanan (12 bulan terakhir)
     * GET /api/laporan/chart/penjualan-bulanan
     */
    public function chartPenjualanBulanan(Request $request)
    {
        if (!in_array($request->user()->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $penjualan = Transaksi::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('total');

            $pembelian = Pembelian::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('total');

            $kerugian = $this->sumKerugianByMonth($month, $year);

            $result[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'bulan_num' => $month,
                'tahun' => $year,
                'total_penjualan' => (float) $penjualan,
                'total_pembelian' => (float) $pembelian,
                'laba_bersih' => (float) ($penjualan - $pembelian - $kerugian),
            ];
        }

        return response()->json($result);
    }

    /**
     * Chart: Metode pembayaran (bulan ini)
     * GET /api/laporan/chart/metode-pembayaran
     */
    public function chartMetodePembayaran(Request $request)
    {
        if (!in_array($request->user()->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $periode = $request->query('periode', 'bulan');
        $query = Transaksi::select(
            'metode_pembayaran',
            DB::raw('COUNT(*) as jumlah_transaksi'),
            DB::raw('SUM(total) as total_nominal')
        );

        if ($periode === 'minggu') {
            $query->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay());
        } elseif ($periode === '3bulan') {
            $query->where('created_at', '>=', Carbon::now()->subMonths(2)->startOfMonth()->startOfDay());
        } else {
            // default: bulan ini
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        $data = $query->groupBy('metode_pembayaran')->get();

        return response()->json($data);
    }

    /**
     * Chart: Top 5 produk terlaris (bulan ini)
     * GET /api/laporan/chart/produk-terlaris
     */
    public function chartProdukTerlaris(Request $request)
    {
        if (!in_array($request->user()->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $limit = intval($request->query('limit', 5));
        if ($limit <= 0) $limit = 5;

        [$start, $end, $prevStart, $prevEnd] = $this->resolveProdukTerlarisPeriode(
            $request->query('periode', 'bulan-ini')
        );

        // Top produk pada periode terpilih (whereBetween = index-friendly untuk data besar)
        $data = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
            ->select(
                'produks.id',
                'produks.nama_produk',
                DB::raw('SUM(transaksi_details.qty) as total_terjual'),
                DB::raw('SUM(transaksi_details.subtotal) as total_pendapatan')
            )
            ->whereBetween('transaksis.created_at', [$start, $end])
            ->groupBy('produks.id', 'produks.nama_produk')
            ->orderByDesc('total_terjual')
            ->limit($limit)
            ->get();

        // Ambil qty periode sebelumnya dalam SATU query (hindari N+1)
        $ids = $data->pluck('id')->all();
        $prevQty = collect();
        if (!empty($ids)) {
            $prevQty = DB::table('transaksi_details')
                ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
                ->whereIn('transaksi_details.produk_id', $ids)
                ->whereBetween('transaksis.created_at', [$prevStart, $prevEnd])
                ->groupBy('transaksi_details.produk_id')
                ->select('transaksi_details.produk_id as produk_id', DB::raw('SUM(transaksi_details.qty) as qty'))
                ->pluck('qty', 'produk_id');
        }

        foreach ($data as $item) {
            $item->total_terjual_bulan_lalu = (int) ($prevQty[$item->id] ?? 0);
        }

        return response()->json($data);
    }

    /**
     * Resolve periode produk terlaris menjadi rentang tanggal [start, end]
     * beserta rentang periode pembanding sebelumnya [prevStart, prevEnd].
     *
     * @return array{0:\Carbon\Carbon,1:\Carbon\Carbon,2:\Carbon\Carbon,3:\Carbon\Carbon}
     */
    private function resolveProdukTerlarisPeriode(string $periode): array
    {
        $now = Carbon::now();

        switch ($periode) {
            case 'bulan-lalu':
                $ref = $now->copy()->subMonth();
                $start = $ref->copy()->startOfMonth();
                $end = $ref->copy()->endOfMonth();
                $prev = $ref->copy()->subMonth();
                $prevStart = $prev->copy()->startOfMonth();
                $prevEnd = $prev->copy()->endOfMonth();
                break;

            case 'tahun-ini':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $prevStart = $now->copy()->subYear()->startOfYear();
                $prevEnd = $now->copy()->subYear()->endOfYear();
                break;

            case 'bulan-ini':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $prev = $now->copy()->subMonth();
                $prevStart = $prev->copy()->startOfMonth();
                $prevEnd = $prev->copy()->endOfMonth();
                break;
        }

        return [$start, $end, $prevStart, $prevEnd];
    }

    public function exportExcel(Request $request)
    {
        $tokenString = $request->query('token');
        if (!$tokenString) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
        if (!$token || !$token->tokenable) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $token->tokenable;
        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $type = $request->query('type');
        $headers = [];
        $rows = [];
        $filename = '';
        $title = '';
        $currencyColumns = []; // columns that should be formatted as IDR currency (0-indexed)

        switch ($type) {
            case 'penjualan-harian':
                $hari = intval($request->query('hari', 30));
                if ($hari <= 0) $hari = 30;

                $title = 'Laporan Penjualan Harian';
                $filename = 'Laporan_Penjualan_Harian_' . Carbon::now()->format('Ymd_His') . '.xlsx';
                
                $startDate = Carbon::now()->subDays($hari - 1)->startOfDay();
                $endDate = Carbon::now()->endOfDay();

                $salesData = Transaksi::select(
                        DB::raw('DATE(created_at) as tanggal'),
                        DB::raw('SUM(total) as total'),
                        DB::raw('COUNT(*) as jumlah_transaksi')
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('tanggal')
                    ->get()
                    ->keyBy('tanggal');

                $headers = ['No', 'Tanggal', 'Total Penjualan (IDR)', 'Jumlah Transaksi'];
                $currencyColumns = [2];
                $currentDate = $startDate->copy();
                $no = 1;
                while ($currentDate <= $endDate) {
                    $dateStr = $currentDate->toDateString();
                    $dateFormatted = $currentDate->translatedFormat('d F Y');
                    $total = (float) ($salesData[$dateStr]->total ?? 0);
                    $jumlahTrx = (int) ($salesData[$dateStr]->jumlah_transaksi ?? 0);
                    
                    $rows[] = [$no++, $dateFormatted, $total, $jumlahTrx];
                    $currentDate->addDay();
                }
                break;

            case 'penjualan-bulanan':
            case 'laba-rugi-bulanan':
                $year = $request->query('tahun');
                $isLabaRugi = ($type === 'laba-rugi-bulanan');
                $title = $isLabaRugi ? 'Laporan Laba Kotor Bulanan' : 'Laporan Penjualan Bulanan';
                $filename = ($isLabaRugi ? 'Laporan_Laba_Kotor_Bulanan_' : 'Laporan_Penjualan_Bulanan_') . ($year ? $year . '_' : '') . Carbon::now()->format('Ymd_His') . '.xlsx';

                $headers = ['No', 'Bulan', 'Tahun', 'Total Penjualan (IDR)', 'Total Pembelian (IDR)', 'Laba Bersih (IDR)'];
                $currencyColumns = [3, 4, 5];
                $no = 1;

                if ($year) {
                    $yearVal = intval($year);
                    for ($month = 1; $month <= 12; $month++) {
                        $date = Carbon::create($yearVal, $month, 1);
                        
                        $penjualan = Transaksi::whereMonth('created_at', $month)
                            ->whereYear('created_at', $yearVal)
                            ->sum('total');

                        $pembelian = Pembelian::whereMonth('created_at', $month)
                            ->whereYear('created_at', $yearVal)
                            ->sum('total');

                        $kerugian = $this->sumKerugianByMonth($month, $yearVal);

                        $rows[] = [
                            $no++,
                            $date->translatedFormat('F'),
                            $yearVal,
                            (float) $penjualan,
                            (float) $pembelian,
                            (float) ($penjualan - $pembelian - $kerugian)
                        ];
                    }
                } else {
                    for ($i = 11; $i >= 0; $i--) {
                        $date = Carbon::now()->subMonths($i);
                        $month = $date->month;
                        $yearVal = $date->year;

                        $penjualan = Transaksi::whereMonth('created_at', $month)
                            ->whereYear('created_at', $yearVal)
                            ->sum('total');

                        $pembelian = Pembelian::whereMonth('created_at', $month)
                            ->whereYear('created_at', $yearVal)
                            ->sum('total');

                        $kerugian = $this->sumKerugianByMonth($month, $yearVal);

                        $rows[] = [
                            $no++,
                            $date->translatedFormat('F'),
                            $yearVal,
                            (float) $penjualan,
                            (float) $pembelian,
                            (float) ($penjualan - $pembelian - $kerugian)
                        ];
                    }
                }
                break;

            case 'metode-pembayaran':
                $title = 'Laporan Metode Pembayaran';
                $filename = 'Laporan_Metode_Pembayaran_' . Carbon::now()->format('Ymd_His') . '.xlsx';

                $periode = $request->query('periode', 'bulan');
                $query = Transaksi::select(
                    'metode_pembayaran',
                    DB::raw('COUNT(*) as jumlah_transaksi'),
                    DB::raw('SUM(total) as total_nominal')
                );

                if ($periode === 'minggu') {
                    $query->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay());
                } elseif ($periode === '3bulan') {
                    $query->where('created_at', '>=', Carbon::now()->subMonths(2)->startOfMonth()->startOfDay());
                } else {
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                }

                $data = $query->groupBy('metode_pembayaran')->get();

                $headers = ['No', 'Metode Pembayaran', 'Jumlah Transaksi', 'Total Nominal (IDR)'];
                $currencyColumns = [3];
                $no = 1;
                foreach ($data as $item) {
                    $rows[] = [
                        $no++,
                        ucfirst($item->metode_pembayaran),
                        $item->jumlah_transaksi,
                        (float) $item->total_nominal
                    ];
                }
                break;

            case 'produk-terlaris':
                $title = 'Laporan Produk Terlaris';
                $filename = 'Laporan_Produk_Terlaris_' . Carbon::now()->format('Ymd_His') . '.xlsx';

                $limit = intval($request->query('limit', 5));
                if ($limit <= 0) $limit = 5;

                [$start, $end, $prevStart, $prevEnd] = $this->resolveProdukTerlarisPeriode(
                    $request->query('periode', 'bulan-ini')
                );

                $data = DB::table('transaksi_details')
                    ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
                    ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
                    ->select(
                        'produks.id',
                        'produks.nama_produk',
                        DB::raw('SUM(transaksi_details.qty) as total_terjual'),
                        DB::raw('SUM(transaksi_details.subtotal) as total_pendapatan')
                    )
                    ->whereBetween('transaksis.created_at', [$start, $end])
                    ->groupBy('produks.id', 'produks.nama_produk')
                    ->orderByDesc('total_terjual')
                    ->limit($limit)
                    ->get();

                $prevIds = $data->pluck('id')->all();
                $prevQtyMap = collect();
                if (!empty($prevIds)) {
                    $prevQtyMap = DB::table('transaksi_details')
                        ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
                        ->whereIn('transaksi_details.produk_id', $prevIds)
                        ->whereBetween('transaksis.created_at', [$prevStart, $prevEnd])
                        ->groupBy('transaksi_details.produk_id')
                        ->select('transaksi_details.produk_id as produk_id', DB::raw('SUM(transaksi_details.qty) as qty'))
                        ->pluck('qty', 'produk_id');
                }

                $headers = [
                    'No',
                    'Nama Produk',
                    'Terjual Periode Ini',
                    'Terjual Periode Lalu',
                    'Perubahan (%)',
                    'Pendapatan Periode Ini (IDR)'
                ];
                $currencyColumns = [5];
                $no = 1;
                foreach ($data as $item) {
                    $totalTerjualBulanLalu = (int) ($prevQtyMap[$item->id] ?? 0);
                    $diffPctLabel = 'Baru';
                    if ($totalTerjualBulanLalu > 0) {
                        $diff = (($item->total_terjual - $totalTerjualBulanLalu) / $totalTerjualBulanLalu) * 100;
                        $diffPctLabel = ($diff >= 0 ? '+' : '') . round($diff, 1) . '%';
                    }

                    $rows[] = [
                        $no++,
                        $item->nama_produk,
                        $item->total_terjual,
                        $totalTerjualBulanLalu,
                        $diffPctLabel,
                        (float) $item->total_pendapatan
                    ];
                }
                break;

            default:
                return response()->json(['message' => 'Invalid export type'], 400);
        }

        return $this->generateExcelResponse($title, $headers, $rows, $filename, $currencyColumns);
    }

    /**
     * Generate a styled Excel (.xlsx) response using PhpSpreadsheet.
     */
    private function generateExcelResponse(string $title, array $headers, array $rows, string $filename, array $currencyColumns = [])
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31)); // Excel max sheet title = 31 chars

        // ── Title Row ──
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B0F19'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // ── Subtitle Row ──
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Retro Komputer POS — Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '94A3B8'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '131926'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        // ── Header Row (row 4) ──
        $headerRow = 4;
        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $header);
        }
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF7A00'], // Retro Orange
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CC6200'],
                ],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // ── Data Rows ──
        $dataStartRow = $headerRow + 1;
        foreach ($rows as $rowIdx => $row) {
            $currentRow = $dataStartRow + $rowIdx;
            foreach ($row as $colIdx => $value) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$colLetter}{$currentRow}", $value);
            }

            // Alternating row colors for readability
            $bgColor = ($rowIdx % 2 === 0) ? 'F8FAFC' : 'EDF2F7';
            $dataRange = "A{$currentRow}:{$lastCol}{$currentRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // ── Currency Formatting ──
        $idrFormat = '#,##0';
        foreach ($currencyColumns as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $lastDataRow = $dataStartRow + count($rows) - 1;
            if ($lastDataRow >= $dataStartRow) {
                $sheet->getStyle("{$colLetter}{$dataStartRow}:{$colLetter}{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode($idrFormat);
            }
        }

        // ── Auto-width columns ──
        foreach (range(1, count($headers)) as $colNum) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // ── Freeze pane below headers ──
        $sheet->freezePane("A" . ($headerRow + 1));

        // ── Auto-filter ──
        $sheet->setAutoFilter("{$headerRange}");

        // ── Write to output ──
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
