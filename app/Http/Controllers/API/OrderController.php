<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use DB;

class OrderController extends Controller
{
    // Checkout / Place Order
    public function placeOrder(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('items.product')
                    ->where('user_id', $user->id)
                    ->first();

        if (!$cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($cart->items->groupBy('product.vendor_id') as $vendorId => $items) {

                $total = 0;

                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_id' => $vendorId,
                    'total_amount' => 0,
                    'status' => 'pending'
                ]);

                foreach ($items as $item) {
                    $price = $item->product->price;
                    $qty = $item->quantity;

                    $total += $price * $qty;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'price' => $price,
                        'quantity' => $qty
                    ]);

                    // Reduce stock
                    $item->product->decrement('stock', $qty);
                }

                $order->update(['total_amount' => $total]);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return response()->json(['message' => 'Order placed successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Customer: View orders
    public function myOrders(Request $request)
    {
        return Order::with('items.product')
                    ->where('user_id', $request->user()->id)
                    ->get();
    }

    // Vendor/Admin: Update order status
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
