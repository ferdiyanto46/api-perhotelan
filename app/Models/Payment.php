<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'external_id', 'payment_method',
        'amount', 'status', 'raw_response',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Tandai pembayaran sebagai berhasil dan update status booking terkait.
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
        $this->booking->update(['status' => 'paid']);
    }

    /**
     * Tandai pembayaran sebagai gagal dan update status booking terkait.
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
        $this->booking->update(['status' => 'failed']);
    }
}