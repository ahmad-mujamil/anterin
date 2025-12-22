<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'driver';

    protected $fillable = [
        'user_id',
        'no_polisi',
        'merek_kendaraan',
        'jenis_kendaraan',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
