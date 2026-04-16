<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model{
    protected $table = 'rooms';
    
    protected $fillable = [
        'room_type_id', 'room_number', 'status',
    ];

    public function roomType() 
    {
        return $this->belongsTo(RoomType::class);
    }
}