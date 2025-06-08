<?php

use App\Http\Controllers\BuffApiController;
use App\Http\Controllers\DMarketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserWatchlistController;

//// Головна сторінка
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Дашборд
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Сторінка імпорту Watchlist
Route::get('/watchlist/import', function () {
    return Inertia::render('settings/ImportWatchlist');
})->name('watchlist.import.view');


Route::post('/watchlist/import', [UserWatchlistController::class, 'import'])
    ->name('watchlist.import');

//Route::middleware(['auth'])->group(function () {
Route::get('/dmarket', [DMarketController::class, 'index'])->name('dmarket.index');
Route::get('/dmarket/market', [DMarketController::class, 'getMarketItems'])->name('dmarket.market');
Route::get('/dmarket/targets', [DMarketController::class, 'getUserTargets'])->name('dmarket.targets');
Route::post('/dmarket/targets', [DMarketController::class, 'createTarget'])->name('dmarket.create');

Route::get('/dmarket/json-view', function () {
    return Inertia::render('DMarketJsonView');
})->name('dmarket.json');


Route::get('/buff/buy-orders', [BuffApiController::class, 'buyOrders'])->name('api.buff.buy-orders');;

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
