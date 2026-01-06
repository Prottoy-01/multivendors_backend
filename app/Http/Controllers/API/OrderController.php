<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use DB;
use Illuminate\Support\Facades\Log;

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

        // 🐛 FIX: Verify all required address fields are present
        if (empty($address->recipient_name) || empty($address->phone) || 
            empty($address->address_line) || empty($address->city)) {
            return response()->json([
                'message' => 'Address is incomplete. Please update your address with all required fields.'
            ], 400);
        }

        // Get user's cart with items and product details
        $cart = Cart::with('items.product.vendor')  // 🐛 FIX: Also load vendor relationship
                    ->where('user_id', $user->id)
                    ->first();

        if (!$cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            // 🐛 FIX: Group items by vendor using closure instead of dot notation
            $groupedItems = $cart->items->groupBy(function($item) {
                if (!$item->product) {
                    throw new \Exception("Product not found for cart item ID: {$item->id}");
                }
                if (!$item->product->vendor_id) {
                    throw new \Exception("Product {$item->product->name} has no vendor assigned");
                }
                return $item->product->vendor_id;
            });

            foreach ($groupedItems as $vendorId => $items) {

                $total = 0;
                $totalDiscount = 0;

                /* ================== 🔹 STEP 3: SAVE ADDRESS IN ORDER ================== */
                $order = Order::create([
                    'user_id'        => $user->id,
                    'vendor_id'      => $vendorId,
                    'total_amount'   => 0,
                    'discount_total' => 0,
                    'status'         => 'pending',
                    // 🔹 Save address snapshot with null coalescing for optional fields
                    'recipient_name' => $address->recipient_name,
                    'phone'          => $address->phone,
                    'address_line'   => $address->address_line,
                    'city'           => $address->city,
                    'state'          => $address->state ?? '',
                    'postal_code'    => $address->postal_code ?? '',
                    'country'        => $address->country ?? 'Bangladesh',
                ]);

                foreach ($items as $item) {
                    $qty = $item->quantity;
                    $originalPrice = $item->price;
                    $finalPrice = $item->final_price ?? $originalPrice; // 🐛 FIX: Handle null final_price

                    $total += $finalPrice * $qty;
                    $totalDiscount += ($originalPrice - $finalPrice) * $qty;

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'price'      => $originalPrice,
                        'final_price'=> $finalPrice,
                        'quantity'   => $qty
                    ]);

                    // Reduce stock (with check to prevent negative stock)
                    if ($item->product->stock >= $qty) {
                        $item->product->decrement('stock', $qty);
                    } else {
                        throw new \Exception("Insufficient stock for product: {$item->product->name}");
                    }
                }

                $order->update([
                    'total_amount'   => $total,
                    'discount_total' => $totalDiscount
                ]);
            }

            // Clear user's cart
            $cart->items()->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // 🐛 FIX: Log the actual error for debugging
            Log::error('Order placement failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Order placement failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Customer: View my orders
     */
    public function myOrders(Request $request)
    {
        return Order::with('items.product')
                    ->where('user_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
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