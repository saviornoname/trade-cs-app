<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistPriceCheck extends Model
{
    protected $fillable = [
        'user_watchlist_item_id',
        'min_market_price_usd',
        'target_max_price_usd',
        'profit_percent',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(UserWatchlistItem::class, 'user_watchlist_item_id');
    }
}
