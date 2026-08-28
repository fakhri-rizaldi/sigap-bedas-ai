<?php

use App\Http\Controllers\Admin\KategoriMappingController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\AduanPublicController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AduanPublicController::class, 'create'])->name('home');
Route::get('/lapor', [AduanPublicController::class, 'create'])->name('lapor.create');
Route::post('/lapor', [AduanPublicController::class, 'store'])->middleware('throttle:15,1')->name('lapor.store');
Route::get('/lapor/sukses/{kodeTiket}', [AduanPublicController::class, 'success'])->name('lapor.success');
Route::get('/lapor/status', [AduanPublicController::class, 'track'])->name('lapor.status');
Route::get('/lapor/status/{kodeTiket}', [AduanPublicController::class, 'track'])->name('lapor.status.detail');

Route::middleware(['auth', 'verified'])->group(function () {
    // CRM Staff Dashboard & Aksi Tiket
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/dashboard/aduan/{aduan}/status', [DashboardController::class, 'updateStatus'])->name('dashboard.aduan.update-status');
    Route::patch('/dashboard/aduan/{aduan}/koreksi', [DashboardController::class, 'koreksiKategori'])->name('dashboard.aduan.koreksi-kategori');

    // Panel Admin: Mapping Kategori Dinas (CRUD)
    Route::get('/admin/kategori-mapping', [KategoriMappingController::class, 'index'])->name('admin.kategori-mapping.index');
    Route::post('/admin/kategori-mapping', [KategoriMappingController::class, 'store'])->name('admin.kategori-mapping.store');
    Route::put('/admin/kategori-mapping/{mapping}', [KategoriMappingController::class, 'update'])->name('admin.kategori-mapping.update');
    Route::delete('/admin/kategori-mapping/{mapping}', [KategoriMappingController::class, 'destroy'])->name('admin.kategori-mapping.destroy');

    // Panel Admin: Statistik Agregat & Ekspor Data
    Route::get('/admin/statistik', [StatistikController::class, 'index'])->name('admin.statistik.index');
    Route::get('/admin/statistik/export-csv', [StatistikController::class, 'exportCsv'])->name('admin.statistik.export-csv');
});

require __DIR__.'/settings.php';

