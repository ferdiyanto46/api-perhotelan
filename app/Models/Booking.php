<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    
    protected $fillable = [
        'user_id', 'room_id', 'check_in','check_out', 'total_price', 'status'
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasOne(Payment::class);
    }

    public function calculateTotalDays($check_in, $check_out)
    {
        $date1 = new \DateTime($check_in);
        $date2 = new \DateTime($check_out);
        return $date2->diff($date1)->format("%a");
    }
}