<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Relasi: Admin-admin yang mengelola hotel ini.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }
}