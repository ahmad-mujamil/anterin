<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'order';

    protected $fillable = [
        'user_id',
        'driver_id',
        'no_order',
        'pickup_address',
        'destination_address',
        'pickup_lat',
        'pickup_lng',
        'destination_lat',
        'destination_lng',
        'distance_km',
        'tarif_dasar',
        'tarif_per_km',
        'total_tarif',
        'status',
        'accepted_at',
        'picked_up_at',
        'completed_at',
        'cancelled_at',

    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function driver() : BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
