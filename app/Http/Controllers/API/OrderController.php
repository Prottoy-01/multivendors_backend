<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use DB;

class OrderController extends Controller
{
    /**
     * Place order / Checkout
     */
    public function placeOrder(Request $request)
    {
        $user = $request->user();

    /* ================== 🔹 STEP 1: VALIDATE ADDRESS ================== */
    $request->validate([
        'address_id' => 'required|exists:user_addresses,id',
    ]);

    /* ================== 🔹 STEP 2: VERIFY ADDRESS BELONGS TO USER ================== */
    $address = $user->addresses()
                    ->where('id', $request->address_id)
                    ->first();

    if (!$address) {
        return response()->json(['message' => 'Invalid address'], 403);
    }

    // Get user's cart with items and product details
    $cart = Cart::with('items.product')
                ->where('user_id', $user->id)
                ->first();

    if (!$cart || $cart->items->count() === 0) {
        return response()->json(['message' => 'Cart is empty'], 400);
    }

    DB::beginTransaction();

    try {
        // Group items by vendor
        foreach ($cart->items->groupBy('product.vendor_id') as $vendorId => $items) {

            $total = 0;
            $totalDiscount = 0;

            /* ================== 🔹 STEP 3: SAVE ADDRESS IN ORDER ================== */
            $order = Order::create([
                'user_id'        => $user->id,
                'vendor_id'      => $vendorId,
               // 'address_id'     => $address->id,   // ✅ ADDED
                'total_amount'   => 0,
                'discount_total' => 0,
                'status'         => 'pending',
                // 🔹 Save address snapshot
                
    'recipient_name' => $address->recipient_name,
    'phone'          => $address->phone,
    'address_line'   => $address->address_line,
    'city'           => $address->city,
    'state'          => $address->state,
    'postal_code'    => $address->postal_code,
    'country'        => $address->country,

            ]);

            foreach ($items as $item) {
                $qty = $item->quantity;
                $originalPrice = $item->price;
                $finalPrice = $item->final_price;

                $total += $finalPrice * $qty;
                $totalDiscount += ($originalPrice - $finalPrice) * $qty;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'price'      => $originalPrice,
                    'final_price'=> $finalPrice,
                    'quantity'   => $qty
                ]);

                // Reduce stock
                $item->product->decrement('stock', $qty);
            }

            $order->update([
                'total_amount'   => $total,
                'discount_total' => $totalDiscount
            ]);
        }

        // Clear user's cart
        $cart->items()->delete();

        DB::commit();
        return response()->json(['message' => 'Order placed successfully']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    /**
     * Customer: View my orders
     */
    public function myOrders(Request $request)
    {
        return Order::with('items.product')
                    ->where('user_id', $request->user()->id)
                    ->get();
    }

    /**
     * Vendor/Admin: Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:paid,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated']);
    }
}
