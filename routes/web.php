<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserWatchlistController;

Route::post('/watchlist/import', [UserWatchlistController::class, 'import'])
    ->name('watchlist.import');


//// Головна сторінка
//Route::get('/', function () {
//    return Inertia::render('Welcome');
//})->name('home');
//
//// Дашборд
//Route::get('/dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
////
//
//// Сторінка імпорту Watchlist
Route::get('/watchlist/import', function () {
    return Inertia::render('settings/ImportWatchlist');
})->name('watchlist.import.view');
//
//Route::post('/watchlist/import', [UserWatchlistController::class, 'import'])
//    ->name('watchlist.import');
////Route::get('/watchlist/import', function () {
////    return Inertia::render('Settings/ImportWatchlist');
////})->middleware(['auth', 'verified'])->name('watchlist.import.view');

// Обробка CSV-імпорту
Route::post('/watchlist/import', [UserWatchlistController::class, 'import'])
    ->middleware(['auth', 'verified'])
    ->name('watchlist.import');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
