<?php

use App\Http\Controllers\Api\AduanClassificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/aduan/classify', [AduanClassificationController::class, 'classify'])
        ->name('api.aduan.classify');

    Route::get('/geocode', [\App\Http\Controllers\Api\GeocodeController::class, 'reverse'])
        ->name('api.geocode.reverse');

    Route::get('/geocode/search', [\App\Http\Controllers\Api\GeocodeController::class, 'search'])
        ->name('api.geocode.search');

    Route::get('/wilayah/kecamatan', [\App\Http\Controllers\Api\WilayahController::class, 'getKecamatan'])
        ->name('api.wilayah.kecamatan');

    Route::get('/wilayah/desa', [\App\Http\Controllers\Api\WilayahController::class, 'getDesa'])
        ->name('api.wilayah.desa');

    Route::get('/aduan/heatmap-data', [\App\Http\Controllers\Api\HeatmapController::class, 'getData'])
        ->name('api.aduan.heatmap');
});
