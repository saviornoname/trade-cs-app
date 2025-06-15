<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserWatchlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function filters(): HasMany
    {
        return $this->hasMany(WatchlistItemFilter::class);
    }

    public function priceChecks(): HasMany
    {
        return $this->hasMany(WatchlistPriceCheck::class);
    }
}
