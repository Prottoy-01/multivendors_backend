<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponUsage extends Model
{
    use HasFactory;

    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Usage belongs to a coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Usage belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usage belongs to an order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}