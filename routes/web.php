<?php

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/dashboard/aduan/{aduan}/status', [DashboardController::class, 'updateStatus'])->name('dashboard.aduan.update-status');
});

require __DIR__.'/settings.php';

