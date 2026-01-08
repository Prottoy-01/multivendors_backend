<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'type',
        'is_filterable',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
    ];

    /**
     * Get all values for this attribute
     */
    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}