<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BarangRusakController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProfilKasirController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/settings/active', [SettingController::class, 'active']);
Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel']);

Route::options('{any}', function (\Illuminate\Http\Request $request) {
    $origin = $request->header('Origin');
    if ($origin && (str_ends_with($origin, '.vercel.app') || str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1') || str_contains($origin, '192.168.') || str_contains($origin, '10.'))) {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-Token, X-Token')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
    return response('', 200);
})->where('any', '.*');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/verify-password', [ProfileController::class, 'verifyPassword']);
    Route::post('/profile/send-otp', [ProfileController::class, 'sendOtp'])->middleware('throttle:3,1');
    Route::post('/profile/verify-otp', [ProfileController::class, 'verifyOtp']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    // Profil Kasir
    Route::get('/profil-kasir', [ProfilKasirController::class, 'index']);
    Route::post('/profil-kasir', [ProfilKasirController::class, 'store']);
    Route::put('/profil-kasir/{id}', [ProfilKasirController::class, 'update']);
    Route::delete('/profil-kasir/{id}', [ProfilKasirController::class, 'destroy']);
    Route::post('/profil-kasir/aktifkan', [ProfilKasirController::class, 'aktifkan']);
    Route::post('/profil-kasir/nonaktifkan', [ProfilKasirController::class, 'nonaktifkan']);
    Route::get('/profil-kasir/aktif', [ProfilKasirController::class, 'getAktif']);
    Route::get('/kasir-users', [ProfilKasirController::class, 'getKasirUsers']);

    // Supplier
    Route::apiResource('suppliers', SupplierController::class);

    // Produk
    Route::apiResource('produk', ProdukController::class);

    // Transaksi (POS)
    Route::get('/transaksi', [TransaksiController::class, 'index']);
    Route::post('/transaksi', [TransaksiController::class, 'store']);
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);
    Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy']);

    // Pembelian (Restock)
    Route::get('/pembelian', [PembelianController::class, 'index']);
    Route::post('/pembelian', [PembelianController::class, 'store']);
    Route::get('/pembelian/{id}', [PembelianController::class, 'show']);

    // Retur
    Route::get('/retur', [ReturController::class, 'index']);
    Route::post('/retur', [ReturController::class, 'store']);

    // Laporan / Dashboard
    Route::get('/laporan/dashboard', [LaporanController::class, 'dashboard']);
    Route::get('/laporan/stok', [LaporanController::class, 'stok']);
    Route::get('/riwayat-stok', [LaporanController::class, 'riwayatStok']);

    // Chart Analytics (Owner & Admin)
    Route::get('/laporan/chart/penjualan-harian', [LaporanController::class, 'chartPenjualanHarian']);
    Route::get('/laporan/chart/penjualan-bulanan', [LaporanController::class, 'chartPenjualanBulanan']);
    Route::get('/laporan/chart/metode-pembayaran', [LaporanController::class, 'chartMetodePembayaran']);
    Route::get('/laporan/chart/produk-terlaris', [LaporanController::class, 'chartProdukTerlaris']);

    // Settings (CRUD)
    Route::post('/settings', [SettingController::class, 'update']);

    // Barang Rusak / Hilang
    Route::get('/barang-rusak', [BarangRusakController::class, 'index']);
    Route::post('/barang-rusak', [BarangRusakController::class, 'store']);
});
