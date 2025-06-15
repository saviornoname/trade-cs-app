<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaintwearRange extends Model
{
    protected $fillable = [
        'name',
        'min',
        'max',
    ];
}
