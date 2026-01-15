<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'price',       // original price
        'final_price', // price after offer/discount
        'quantity',
        'variant_id', // ✅ ADD THIS
        'variant_details', // ✅ ADD THIS
    ];

    protected $casts = [
        'variant_details' => 'array', // ✅ ADD THIS
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the vendor through the product relationship
     * Note: Use product.vendor for eager loading, not items.vendor
     */

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get display name with variant
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->product->name ?? 'Product Deleted';
        
        if ($this->variant_details) {
            $variantInfo = [];
            foreach ($this->variant_details['attributes'] ?? [] as $key => $value) {
                $variantInfo[] = ucfirst($key) . ': ' . ucfirst($value);
            }
            if (!empty($variantInfo)) {
                $name .= ' (' . implode(', ', $variantInfo) . ')';
            }
        }
        
        return $name;
    }
}