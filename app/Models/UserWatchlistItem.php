<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
