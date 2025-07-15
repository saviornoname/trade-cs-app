<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//// Головна сторінка
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware([])->name('dashboard');
Route::get('/dashboard/watchlist/prices', fn () => Inertia::render('WatchlistCheckPrices'))
    ->name('dashboard.watchlist.prices');
Route::get('/dashboard/watchlist', fn () => Inertia::render('UserWatchlist'))
    ->name('dashboard.watchlist');

require __DIR__.'/watchlist.php';
require __DIR__.'/dmarket.php';
require __DIR__.'/buff.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

