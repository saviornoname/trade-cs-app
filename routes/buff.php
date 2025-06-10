<?php

use App\Http\Controllers\BuffApiController;
use Illuminate\Support\Facades\Route;

Route::get('/buff/buy-orders', [BuffApiController::class, 'buyOrders'])
    ->name('api.buff.buy-orders');

Route::get('/buff/sell-orders', [BuffApiController::class, 'sellOrders'])
    ->name('api.buff.sell-orders');
