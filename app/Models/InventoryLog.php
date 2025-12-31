<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'user_id',
        'order_id',
        'notes',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    /**
     * Reasons for inventory changes
     */
    const REASON_SALE = 'sale';
    const REASON_RESTOCK = 'restock';
    const REASON_RETURN = 'return';
    const REASON_ADJUSTMENT = 'adjustment';
    const REASON_DAMAGED = 'damaged';
    const REASON_LOST = 'lost';

    /**
     * Log belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Log belongs to a user (who made the change)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log belongs to an order (if change is due to sale/return)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Create a log entry for sale
     */
    public static function logSale($product, $quantity, $orderId)
    {
        return self::create([
            'product_id' => $product->id,
            'quantity_change' => -$quantity,
            'quantity_before' => $product->stock + $quantity,
            'quantity_after' => $product->stock,
            'reason' => self::REASON_SALE,
            'order_id' => $orderId,
        ]);
    }

    /**
     * Create a log entry for restock
     */
    public static function logRestock($product, $quantity, $userId, $notes = null)
    {
        return self::create([
            'product_id' => $product->id,
            'quantity_change' => $quantity,
            'quantity_before' => $product->stock - $quantity,
            'quantity_after' => $product->stock,
            'reason' => self::REASON_RESTOCK,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }
}
//future implementation