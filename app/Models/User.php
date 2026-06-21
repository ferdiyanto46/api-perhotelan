<?php

// namespace App\Models;

// use Illuminate\Auth\Authenticatable;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Laravel\Lumen\Auth\Authorizable;

// class User extends Model implements AuthenticatableContract, AuthorizableContract
// {
//     use Authenticatable, Authorizable, HasFactory;

//     protected $table='users';

//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var string[]
//      */
//     protected $fillable = [
//         'name', 'email', 'password','remember_token',
//     ];

//     /**
//      * The attributes excluded from the model's JSON form.
//      *
//      * @var string[]
//      */
//     protected $hidden = [
//         'password','remember_token'
//     ];


//     public function roles()
//     {
//         $this->belongsToMany(Role::class , 'role_user');
//     }

//     public function hasRole($roleName)
//     {
//         return $this->roles()->where('name', $roleName)->exists();
//     }
// }

// app/Models/User.php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $fillable = [
        'name', 'email', 'password', 'remember_token', 'role', 'hotel_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Jika Anda memutuskan untuk kembali ke relasi many-to-many dengan model Role terpisah,
    // Anda bisa mengaktifkan kembali metode ini. Untuk saat ini, role disimpan langsung di tabel users.
    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'role_user');
    // }

    /**
     * Relasi: Admin terikat ke satu Hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Cek apakah user memiliki/mengelola hotel dengan ID tertentu.
     * Super Admin otomatis memiliki akses ke semua hotel.
     */
    public function ownsHotel($hotelId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (int) $this->hotel_id === (int) $hotelId;
    }

    /**
     * Check if the user has the given role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role === $roleName;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
}
