<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'has_offer',
        'discount_type',
        'discount_value',
        'offer_start',
        'offer_end',
    ];

    /**
     * Automatically include final_price in API responses
     */
    protected $appends = ['final_price'];

    /**
     * Relationships
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check if offer is active
     */
    public function isOfferActive(): bool
    {
        if (!$this->has_offer) {
            return false;
        }

        $now = Carbon::now();

        if ($this->offer_start && $now->lt($this->offer_start)) {
            return false;
        }

        if ($this->offer_end && $now->gt($this->offer_end)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate final price (used by frontend)
     */
    public function getFinalPriceAttribute()
    {
        if (!$this->isOfferActive()) {
            return (float) $this->price;
        }

        if ($this->discount_type === 'percentage') {
            return round(
                $this->price - ($this->price * ($this->discount_value / 100)),
                2
            );
        }

        if ($this->discount_type === 'fixed') {
            return max(0, round($this->price - $this->discount_value, 2));
        }

        return (float) $this->price;
    }
}
