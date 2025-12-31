<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'is_approved',
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'rating' => 'integer',
    ];

    /**
     * Review belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Review belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Review belongs to an order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Review has many votes
     */
    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Get helpful votes count
     */
    public function getHelpfulVotesCountAttribute()
    {
        return $this->votes()->where('is_helpful', true)->count();
    }

    /**
     * Get not helpful votes count
     */
    public function getNotHelpfulVotesCountAttribute()
    {
        return $this->votes()->where('is_helpful', false)->count();
    }
}