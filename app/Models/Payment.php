<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'transaction_id',
        'payment_gateway',
        'amount',
        'status',
        'gateway_response',
        'failure_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Payment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment methods
     */
    const METHOD_CARD = 'card';
    const METHOD_MOBILE_BANKING = 'mobile_banking';
    const METHOD_COD = 'cod';

    /**
     * Payment belongs to an order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);
    }
}
//future implementation