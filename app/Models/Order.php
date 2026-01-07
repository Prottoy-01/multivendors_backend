<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
         // User & Vendor
        'user_id',
        'vendor_id',
        
        // Money fields (ALL REQUIRED - match your database)
        'total_amount',
        'discount_total',
        'coupon_id',
        'coupon_discount',
        'tax_amount',
        'shipping_cost',
        'grand_total',        // ✅ THIS WAS MISSING - causing error
        
        // Status fields
        'status',
        'payment_method',
        'payment_status',
        
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

    public function coupon()//new
{
    return $this->belongsTo(Coupon::class);
}

    /* ================= Order Status ================= */

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
}
