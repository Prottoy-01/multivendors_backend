<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewVote extends Model
{
    protected $fillable = [
        'user_id',
        'review_id',
        'is_helpful',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    /**
     * Vote belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vote belongs to a review
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}

//this is optional model