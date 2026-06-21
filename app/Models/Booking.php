<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'room_id', 'check_in', 'check_out',
        'total_price', 'status',
    ];

    protected $casts = [
        'check_in'    => 'date',
        'check_out'   => 'date',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Accessor: hitung jumlah malam berdasarkan check_in & check_out.
     */
    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }
}