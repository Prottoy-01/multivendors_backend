<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'status',
        'commission_percentage',
        'total_earnings',
        'address'  ,// ✅ ADD THIS

        'shop_description', // ⭐ ADDED
    'phone',            // ⭐ ADDED
        
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'total_earnings' => 'decimal:2',  // ✅ ADDED
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
