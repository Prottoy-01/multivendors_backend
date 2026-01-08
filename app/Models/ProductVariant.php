<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'attributes',
        'price',
        'stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the variant
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock()
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Get color attribute
     */
    public function getColorAttribute()
    {
        return $this->attributes['color'] ?? null;
    }

    /**
     * Get size attribute
     */
    public function getSizeAttribute()
    {
        return $this->attributes['size'] ?? null;
    }

    /**
     * Get formatted attributes
     */
    public function getFormattedAttributesAttribute()
    {
        $formatted = [];
        foreach ($this->attributes as $key => $value) {
            $formatted[] = ucfirst($key) . ': ' . ucfirst($value);
        }
        return implode(', ', $formatted);
    }
}