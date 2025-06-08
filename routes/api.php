<?php

use App\Http\Controllers\BuffApiController;
use App\Http\Controllers\DMarketController;

Route::middleware('auth:sanctum')->prefix('dmarket')->group(function () {
    Route::get('/targets', [DMarketController::class, 'getUserTargets']);
    Route::get('/market', [DMarketController::class, 'getMarketTargets']);
    Route::post('/targets', [DMarketController::class, 'createTarget']);
    Route::get('/compare', [DMarketController::class, 'compareWithBuff']);
});
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/buff-test', [BuffApiController::class, 'index']);
    Route::get('/buff/buy-orders', [BuffApiController::class, 'buyOrders'])
        ->name('api.buff.buy-orders');
});
