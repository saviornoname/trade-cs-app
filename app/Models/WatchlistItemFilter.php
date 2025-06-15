<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistItemFilter extends Model
{
    protected $fillable = [
        'user_watchlist_item_id',
        'min_float',
        'max_float',
        'paint_seed',
        'phase',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(UserWatchlistItem::class, 'user_watchlist_item_id');
    }
}
