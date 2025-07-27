<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistItemFilter extends Model
{
    protected $fillable = [
        'user_watchlist_item_id',
        'paintwear_range_id',
        'paint_seed',
        'phase',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(UserWatchlistItem::class, 'user_watchlist_item_id');
    }

    public function floatRange(): BelongsTo
    {
        return $this->belongsTo(PaintwearRange::class, 'paintwear_range_id');
    }
}
