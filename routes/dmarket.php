<?php

use App\Http\Controllers\DMarketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/dmarket', [DMarketController::class, 'index'])->name('dmarket.index');
Route::get('/dmarket/market', [DMarketController::class, 'getMarketItems'])->name('dmarket.market');
Route::get('/dmarket/targets', [DMarketController::class, 'getUserTargets'])->name('dmarket.targets');
Route::post('/dmarket/targets', [DMarketController::class, 'createTarget'])->name('dmarket.create');
Route::get('/dmarket/compare', [DMarketController::class, 'compareWithBuff'])->name('dmarket.compare');

Route::get('/dmarket/json-view', fn () => Inertia::render('DMarketJsonView'))
    ->name('dmarket.json');
