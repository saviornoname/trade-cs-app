<?php

use App\Http\Controllers\UserWatchlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/watchlist', fn () => Inertia::render('WatchlistOrders'))
    ->name('watchlist.view');

Route::get('/watchlist/import', fn () => Inertia::render('settings/ImportWatchlist'))
    ->name('watchlist.import.view');

Route::post('/watchlist/import', [UserWatchlistController::class, 'import'])
    ->name('watchlist.import');

Route::get('/watchlist/items', [UserWatchlistController::class, 'index'])
    ->name('watchlist.items');

Route::patch('/watchlist/items/{item}', [UserWatchlistController::class, 'toggleActive'])
    ->name('watchlist.toggle');

Route::post('/watchlist/deactivate-all', [UserWatchlistController::class, 'deactivateAll'])
    ->name('watchlist.deactivateAll');

Route::post('/watchlist/activate-all', [UserWatchlistController::class, 'activateAll'])
    ->name('watchlist.activateAll');
