<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'cancelled_by',
        'cancellation_reason',
        'order_status_at_cancellation',
        'original_amount',
        'refund_amount',
        'refund_percentage',
        'vendor_retention',
        'refund_status',
        'refund_processed_at',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refund_percentage' => 'decimal:2',
        'vendor_retention' => 'decimal:2',
        'refund_processed_at' => 'datetime',
    ];

    /**
     * Refund statuses
     */
    const REFUND_PENDING = 'pending';
    const REFUND_PROCESSING = 'processing';
    const REFUND_COMPLETED = 'completed';
    const REFUND_FAILED = 'failed';

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}