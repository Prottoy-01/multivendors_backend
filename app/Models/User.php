<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;//late change
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\UserAddress;
use Laravel\Sanctum\HasApiTokens;//late change




class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;//LATE CHANGE

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status', // ✅ ADD THIS
        'avatar',
        // 🔵 REQUIRED FOR AUTH
    'role',
    'google_id',
    'auth_provider',
    'wallet_balance',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
        ];
    }

    /**
     * 🔗 User has many addresses
     */

    public function addresses()
{
    return $this->hasMany(UserAddress::class);
}

public function reviews()
{
    return $this->hasMany(Review::class);
}

public function wishlist()
{
    return $this->hasMany(Wishlist::class);
}

public function orders()
{
    return $this->hasMany(Order::class);
}

 public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isBanned()
    {
        return $this->status === 'banned';
    }

}