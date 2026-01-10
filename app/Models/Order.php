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
        'order_number',        // ✅ ADDED
        'notes',              // ✅ ADDED
        
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
        'transaction_id',     // ✅ ADDED
        
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

    // ✅ ADDED: Payment relationship
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /* ================= Order Status ================= */

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
}