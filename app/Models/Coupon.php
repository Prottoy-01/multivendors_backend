<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_user_limit' => 'integer',
    ];

    /**
     * Coupon types
     */
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';
    const TYPE_FREE_SHIPPING = 'free_shipping';

    /**
     * Coupon has many usages
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Coupon created by admin
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if coupon is valid
     */
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check date validity
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedBy($userId)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check per-user limit
        $userUsageCount = $this->usages()
            ->where('user_id', $userId)
            ->count();

        return $userUsageCount < $this->per_user_limit;
    }

    /**
     * Calculate discount for given amount
     */
    public function calculateDiscount($amount)
    {
        if (!$this->isValid()) {
            return 0;
        }

        // Check minimum purchase
        if ($this->min_purchase && $amount < $this->min_purchase) {
            return 0;
        }

        $discount = 0;

        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = ($amount * $this->value) / 100;

            // Apply max discount cap
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
        } elseif ($this->type === self::TYPE_FIXED) {
            $discount = min($this->value, $amount);
        }

        return round($discount, 2);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Scope: Active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid coupons (active and within date range)
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $now);
            });
    }
}