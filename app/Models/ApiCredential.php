<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'buff_cookie',
        'dmarket_public_key',
        'dmarket_secret_key',
    ];
}
