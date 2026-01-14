<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        // User & Vendor
        'user_id',
        'vendor_id',
        
        // Order Info
        'order_number',
        'notes',
        
        // Money fields
        'total_amount',
        'discount_total',
        'coupon_id',
        'coupon_discount',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        
        // Status & Payment
        'status',
        'payment_method',
        'payment_status',
        'transaction_id',
        
        // Address fields
        'recipient_name',
        'phone',
        'address_line',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
    ];

    /* ================= Relationships ================= */

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ✅ NEW: Cancellation relationship
    public function cancellation()
    {
        return $this->hasOne(OrderCancellation::class);
    }

    /* ================= Order Status ================= */

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /* ================= Helper Methods ================= */

    /**
     * Check if order can be cancelled by customer
     */
    public function canBeCancelledByCustomer(): bool
    {
        // Cannot cancel if already cancelled or delivered
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_DELIVERED])) {
            return false;
        }

        // Can cancel if not yet shipped or just shipped
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_PROCESSING, self::STATUS_SHIPPED]);
    }

    /**
     * Calculate refund percentage based on order status
     */
    public function getRefundPercentage(): float
    {
        // If shipped, customer gets 40% refund
        if ($this->status === self::STATUS_SHIPPED) {
            return 40.00;
        }

        // If not yet shipped, customer gets 100% refund
        if (in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_PROCESSING])) {
            return 100.00;
        }

        // Cannot refund if delivered or already cancelled
        return 0.00;
    }

    /**
     * Calculate refund amount
     */
    public function calculateRefundAmount(): float
    {
        $percentage = $this->getRefundPercentage();
        return round(($this->grand_total * $percentage) / 100, 2);
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}