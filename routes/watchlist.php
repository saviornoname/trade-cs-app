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

Route::patch('/watchlist/items/{item}/update', [UserWatchlistController::class, 'update'])
    ->name('watchlist.update');

Route::delete('/watchlist/items/{item}', [UserWatchlistController::class, 'destroy'])
    ->name('watchlist.delete');

Route::post('/watchlist/deactivate-all', [UserWatchlistController::class, 'deactivateAll'])
    ->name('watchlist.deactivateAll');

Route::post('/watchlist/activate-all', [UserWatchlistController::class, 'activateAll'])
    ->name('watchlist.activateAll');

Route::get('/watchlist/items/{item}/filters', [UserWatchlistController::class, 'filters'])
    ->name('watchlist.filters');

Route::post('/watchlist/items/{item}/filters', [UserWatchlistController::class, 'addFilter'])
    ->name('watchlist.filters.add');

Route::put('/watchlist/items/{item}/filters', [UserWatchlistController::class, 'updateFilters'])
    ->name('watchlist.filters.update');

Route::delete('/watchlist/filters/{filter}', [UserWatchlistController::class, 'deleteFilter'])
    ->name('watchlist.filters.delete');

Route::patch('/watchlist/filters/{filter}', [UserWatchlistController::class, 'updateFilter'])
    ->name('watchlist.filters.update-one');

Route::get('/float-ranges', function () {
    return response()->json(\App\Models\PaintwearRange::all());
})->name('float-ranges.list');
