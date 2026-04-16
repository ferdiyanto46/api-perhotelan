<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model{
    protected $table = 'room_types';
    
    protected $fillable = [
        'hotel_id', 'name', 'capacity','price_per_night', 'img_url'
    ];


    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->hasMany(Room::class);
    }
}