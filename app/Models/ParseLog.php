<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParseLog extends Model
{
    protected $fillable = [
        'source',
        'status',
        'message',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
