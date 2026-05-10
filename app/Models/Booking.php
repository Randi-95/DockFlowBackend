<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_number',
        'user_id',
        'vessel_id',
        'dock_location',
        'estimated_delivery_date',
        'total_estimated_price',
        'status',
        'created_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}
