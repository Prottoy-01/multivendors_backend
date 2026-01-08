<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
       // 'user_id',
       'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'final_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayNameAttribute()
    {
        $name = $this->product->name;
        
        if ($this->variant) {
            $name .= ' (' . $this->variant->name . ')';
        }
        
        return $name;
    }


     /**
     * Get the effective price (variant price or product price)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->variant ? $this->variant->price : $this->product->price;
    }

    /**
     * Get available stock
     */
    public function getAvailableStockAttribute()
    {
        return $this->variant ? $this->variant->stock : $this->product->stock;
    }
}