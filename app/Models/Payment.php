<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'booking_id','external_id','payment_method','amount','status','raw_response'
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function booking(){
        return $this->belongsTo(Booking::class);
    }
}