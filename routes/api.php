<?php

Route::middleware('auth:sanctum')->prefix('dmarket')->group(function () {
    Route::get('/targets', [DMarketController::class, 'getUserTargets']);
    Route::get('/market', [DMarketController::class, 'getMarketTargets']);
    Route::post('/targets', [DMarketController::class, 'createTarget']);
    Route::get('/compare', [DMarketController::class, 'compareWithBuff']);
});
