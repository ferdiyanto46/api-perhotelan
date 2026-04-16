<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model{
    protected $table = 'hotels';
    
    protected $fillable = [
        'name', 'city', 'address','description', 'rating', 'img_url'
    ];

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }
}