<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $table = 'tarif';

    protected $fillable = [
        'min_km',
        'harga_dasar',
        'harga_per_km',
        'is_active',
    ];
}
