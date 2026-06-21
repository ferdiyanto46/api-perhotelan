<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_type_id', 'room_number', 'status',
        'description', 'price', 'img_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Cek apakah kamar tersedia di rentang tanggal yang diminta.
     */
    public function isAvailable($checkIn, $checkOut): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $conflictingBooking = $this->bookings()
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();

        return !$conflictingBooking;
    }

    /**
     * Scope: filter kamar yang tersedia di rentang tanggal tertentu.
     */
    public function scopeAvailableForDates($query, $checkIn, $checkOut)
    {
        return $query->where('status', 'available')
            ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereNotIn('status', ['cancelled', 'failed'])
                  ->where(function ($sub) use ($checkIn, $checkOut) {
                      $sub->whereBetween('check_in', [$checkIn, $checkOut])
                          ->orWhereBetween('check_out', [$checkIn, $checkOut])
                          ->orWhere(function ($s) use ($checkIn, $checkOut) {
                              $s->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                          });
                  });
            });
    }
}
