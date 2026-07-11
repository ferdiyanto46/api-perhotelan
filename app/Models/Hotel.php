<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Hotel extends Model
{
    protected $fillable = [
        'name', 'city', 'address', 'description',
        'rating', 'img_url',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    /**
     * Relasi: Semua kamar fisik di hotel ini (melalui room_types).
     */
    public function rooms(): HasManyThrough
    {
        return $this->hasManyThrough(Room::class, RoomType::class);
    }

    /**
     * Relasi: Admin-admin yang mengelola hotel ini.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }
}