<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWatchlistItem extends Model
{
    protected $fillable = [
        'title',
        'max_price_usd',
        'min_float',
        'max_float',
        'item_id',
        'phase',
        'paint_seed',
        'active',
    ];
}
