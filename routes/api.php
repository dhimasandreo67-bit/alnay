<?php

use App\Http\Controllers\Api\WilayahController;
use Illuminate\Support\Facades\Route;

Route::prefix('wilayah')->group(function () {
    Route::get('/provinces', [WilayahController::class, 'provinces']);
    Route::get('/cities/{provinceId}', [WilayahController::class, 'cities']);
    Route::get('/districts/{cityId}', [WilayahController::class, 'districts']);
});