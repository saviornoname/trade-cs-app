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

Route::get('/watchlist/items', [UserWatchlistController::class, 'index'])
    ->name('watchlist.items');
Route::patch('/watchlist/items/{item}', [UserWatchlistController::class, 'toggleActive'])
    ->name('watchlist.toggle');
Route::post('/watchlist/deactivate-all', [UserWatchlistController::class, 'deactivateAll'])->name('watchlist.deactivateAll');
Route::post('/watchlist/activate-all', [UserWatchlistController::class, 'activateAll'])->name('watchlist.activateAll');

Route::get('/watchlist', function () {
    return Inertia::render('WatchlistOrders');
})->name('watchlist.view');

//Route::middleware(['auth'])->group(function () {
Route::get('/dmarket', [DMarketController::class, 'index'])->name('dmarket.index');
Route::get('/dmarket/market', [DMarketController::class, 'getMarketItems'])->name('dmarket.market');
Route::get('/dmarket/targets', [DMarketController::class, 'getUserTargets'])->name('dmarket.targets');
Route::post('/dmarket/targets', [DMarketController::class, 'createTarget'])->name('dmarket.create');
Route::get('/dmarket/compare', [DMarketController::class, 'compareWithBuff'])->name('dmarket.compare');

Route::get('/dmarket/json-view', function () {
    return Inertia::render('DMarketJsonView');
})->name('dmarket.json');


Route::get('/buff/buy-orders', [BuffApiController::class, 'buyOrders'])->name('api.buff.buy-orders');;
Route::get('/buff/sell-orders', [BuffApiController::class, 'sellOrders'])
    ->name('api.buff.sell-orders');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
