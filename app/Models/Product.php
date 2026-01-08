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
        'has_offer',
        'discount_type',
        'discount_value',
        'offer_start',
        'offer_end',
        'avg_rating',
    'review_count',
    ];

    /**
     * Automatically include final_price and images in API responses
     */
    protected $appends = ['final_price', 'image_urls'];


        /**
     * Get all variants for this product
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get active variants only
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true)->where('stock', '>', 0);
    }

    /**
     * Check if product has variants
     */
    public function hasVariants()
    {
        return $this->variants()->count() > 0;
    }

    /**
     * Get available colors from variants
     */
    public function getAvailableColorsAttribute()
    {
        return $this->activeVariants()
            ->get()
            ->pluck('attributes.color')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Get available sizes from variants
     */
    public function getAvailableSizesAttribute()
    {
        return $this->activeVariants()
            ->get()
            ->pluck('attributes.size')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Get all unique attribute types from variants
     */
    public function getVariantAttributesAttribute()
    {
        $attributes = [];
        foreach ($this->activeVariants as $variant) {
            foreach ($variant->attributes as $key => $value) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = [];
                }
                if (!in_array($value, $attributes[$key])) {
                    $attributes[$key][] = $value;
                }
            }
        }
        return $attributes;
    }

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

    public function reviews()//new
{
    return $this->hasMany(Review::class);
}

public function wishlists()//new
{
    return $this->hasMany(Wishlist::class);
}

    /**
     * Product images relationship
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Return images as URLs for API response
     */
    public function getImageUrlsAttribute()
    {
        //return $this->images->map(fn($img) => asset('storage/' . $img->image_path));
        return $this->images->map(fn($img) => asset('storage/' . $img->image_path));
    }

    /**
     * Check if offer is active
     */
    public function isOfferActive(): bool
    {
        if (!$this->has_offer) return false;

        $now = Carbon::now();
        if ($this->offer_start && $now->lt($this->offer_start)) return false;
        if ($this->offer_end && $now->gt($this->offer_end)) return false;

        return true;
    }

    /**
     * Calculate final price (used by frontend)
     */
    public function getFinalPriceAttribute()
    {
        if (!$this->isOfferActive()) return (float) $this->price;

        if ($this->discount_type === 'percentage') {
            return round($this->price - ($this->price * ($this->discount_value / 100)), 2);
        }

        if ($this->discount_type === 'fixed') {
            return max(0, round($this->price - $this->discount_value, 2));
        }

        return (float) $this->price;
    }
}
