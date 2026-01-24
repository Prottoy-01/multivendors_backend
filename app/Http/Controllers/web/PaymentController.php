<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    /**
     * ✅ FIXED: Create Stripe Checkout Session with Coupon Support
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get cart
            $cart = Cart::where('user_id', $user->id)->first();
            if (!$cart) {
                return back()->with('error', 'Cart is empty');
            }
            
            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with(['product', 'variant'])
                ->get();
            
            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Cart is empty');
            }
            
            // Check stock availability
            foreach ($cartItems as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if (!$variant || $variant->stock < $item->quantity) {
                        return back()->with('error', "Insufficient stock for {$item->product->name}. Only {$variant->stock} available.");
                    }
                } else {
                    if ($item->product->stock < $item->quantity) {
                        return back()->with('error', "Insufficient stock for {$item->product->name}. Only {$item->product->stock} available.");
                    }
                }
            }
            
            // Get shipping address
            $shippingAddress = UserAddress::where('user_id', $user->id)
                ->where('id', $request->address_id)
                ->first();
            
            if (!$shippingAddress) {
                return back()->with('error', 'Please select a shipping address');
            }
            
            // Calculate subtotal
            $subtotal = $cartItems->sum(function($item) {
                return $item->quantity * $item->final_price;
            });
            
            // Get coupon info from session
            $couponDiscount = 0;
            $couponId = null;
            $couponCode = null;
            $appliedCoupon = session('applied_coupon');
            
            if ($appliedCoupon) {
                $couponDiscount = floatval($appliedCoupon['discount'] ?? 0);
                $couponId = $appliedCoupon['id'] ?? null;
                $couponCode = $appliedCoupon['code'] ?? null;
            }
            
            // Calculate final amounts
            $subtotalAfterDiscount = max(0, $subtotal - $couponDiscount);
            $tax = $subtotalAfterDiscount * 0.10;
            $total = $subtotalAfterDiscount + $tax;
            
            // Validate total is positive
            if ($total <= 0) {
                return back()->with('error', 'Invalid order total. Please try again.');
            }
            
            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // ✅ BUILD LINE ITEMS - Stripe doesn't allow negative amounts!
            $lineItems = [];
            
            if ($couponDiscount > 0 && $subtotal > 0) {
                // Apply discount proportionally to each item
                foreach ($cartItems as $item) {
                    $itemTotal = $item->quantity * $item->final_price;
                    $itemDiscountRatio = $itemTotal / $subtotal;
                    $itemDiscount = $couponDiscount * $itemDiscountRatio;
                    $itemPriceAfterDiscount = ($itemTotal - $itemDiscount) / $item->quantity;
                    
                    // Ensure price is positive and at least 1 cent
                    $finalUnitPrice = max(0.01, $itemPriceAfterDiscount);
                    
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $item->product->name,
                                'description' => $couponCode ? "Discount applied: {$couponCode}" : 'Product',
                            ],
                            'unit_amount' => (int)round($finalUnitPrice * 100),
                        ],
                        'quantity' => $item->quantity,
                    ];
                }
            } else {
                // No discount - use regular prices
                foreach ($cartItems as $item) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $item->product->name,
                                'description' => 'Product',
                            ],
                            'unit_amount' => (int)round($item->final_price * 100),
                        ],
                        'quantity' => $item->quantity,
                    ];
                }
            }
            
            // Add tax as separate line item
            if ($tax > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Tax (10%)',
                        ],
                        'unit_amount' => (int)round($tax * 100),
                    ],
                    'quantity' => 1,
                ];
            }
            
            // Store cart data for order creation after payment
            $simplifiedCartItems = $cartItems->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'original_price' => $item->product->price,
                    'final_price' => $item->final_price,
                    'discount_type' => $item->discount_type ?? null,
                    'discount_value' => $item->discount_value ?? 0,
                    'vendor_id' => $item->product->vendor_id,
                    'product_name' => $item->product->name,
                ];
            })->toArray();
            
            // Store order data in session
            session([
                'pending_order_data' => [
                    'address_id' => $request->address_id,
                    'notes' => $request->notes ?? null,
                    'cart_items' => $simplifiedCartItems,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'coupon_id' => $couponId,
                    'coupon_code' => $couponCode,
                    'coupon_discount' => $couponDiscount,
                    'total' => $total,
                    'shipping_address' => [
                        'recipient_name' => $shippingAddress->recipient_name,
                        'phone' => $shippingAddress->phone,
                        'address_line' => $shippingAddress->address_line,
                        'city' => $shippingAddress->city,
                        'state' => $shippingAddress->state,
                        'postal_code' => $shippingAddress->postal_code,
                        'country' => $shippingAddress->country,
                    ],
                ]
            ]);
            
            // Create Stripe Checkout Session
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.failed'),
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'address_id' => $request->address_id,
                ],
            ];
            
            // Add coupon info to metadata if exists
            if ($couponCode) {
                $sessionData['metadata']['coupon_code'] = $couponCode;
                $sessionData['metadata']['coupon_discount'] = $couponDiscount;
            }
            
            $session = StripeSession::create($sessionData);
            
            return redirect($session->url);
            
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('payment.failed')
                ->with('error', 'Payment processing error: ' . $e->getMessage());
        }
    }
    
    /**
     * ✅ FIXED: Handle successful payment from Stripe
     */
    public function handleSuccess(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            
            if (!$sessionId) {
                return redirect()->route('payment.failed')
                    ->with('error', 'Invalid payment session');
            }
            
            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Retrieve session
            $session = StripeSession::retrieve($sessionId);
            
            if ($session->payment_status !== 'paid') {
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment was not completed');
            }
            
            // Get order data from session
            $orderData = session('pending_order_data');
            if (!$orderData) {
                return redirect()->route('payment.failed')
                    ->with('error', 'Order data not found. Please try again.');
            }
            
            if (!isset($orderData['shipping_address'])) {
                return redirect()->route('payment.failed')
                    ->with('error', 'Shipping address not found');
            }
            
            $user = Auth::user();
            $shippingAddress = $orderData['shipping_address'];
            
            DB::beginTransaction();
            
            try {
                // Group items by vendor
                $cartItems = collect($orderData['cart_items']);
                $itemsByVendor = $cartItems->groupBy('vendor_id');
                
                $allOrders = [];
                $firstOrder = null;
                
                foreach ($itemsByVendor as $vendorId => $vendorItems) {
                    // Calculate vendor totals
                    $vendorSubtotal = $vendorItems->sum(function($item) {
                        return $item['quantity'] * $item['final_price'];
                    });
                    
                    // Apply coupon discount proportionally
                    $vendorCouponDiscount = 0;
                    if ($orderData['coupon_discount'] > 0 && $orderData['subtotal'] > 0) {
                        $vendorCouponDiscount = ($vendorSubtotal / $orderData['subtotal']) * $orderData['coupon_discount'];
                    }
                    
                    $vendorTax = ($vendorSubtotal - $vendorCouponDiscount) * 0.10;
                    $vendorTotal = $vendorSubtotal - $vendorCouponDiscount + $vendorTax;
                    
                    // Create order
                    $order = Order::create([
                        'user_id' => $user->id,
                        'vendor_id' => $vendorId,
                        'order_number' => 'ORD-' . strtoupper(uniqid()),
                        'total_amount' => $vendorSubtotal,
                        'tax_amount' => $vendorTax,
                        'shipping_cost' => 0,
                        'coupon_id' => $orderData['coupon_id'] ?? null,
                        'coupon_discount' => $vendorCouponDiscount,
                        'grand_total' => $vendorTotal,
                        'status' => 'pending',
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'transaction_id' => $session->payment_intent,
                        'notes' => $orderData['notes'] ?? null,
                        'recipient_name' => $shippingAddress['recipient_name'],
                        'phone' => $shippingAddress['phone'],
                        'address_line' => $shippingAddress['address_line'],
                        'city' => $shippingAddress['city'],
                        'state' => $shippingAddress['state'],
                        'postal_code' => $shippingAddress['postal_code'],
                        'country' => $shippingAddress['country'],
                    ]);
                    
                    if (!$firstOrder) {
                        $firstOrder = $order;
                    }
                    
                    $allOrders[] = $order;
                    
                    // Create order items and deduct stock
                    foreach ($vendorItems as $itemData) {
                        // Deduct stock
                        if ($itemData['variant_id']) {
                            $variant = ProductVariant::find($itemData['variant_id']);
                            if ($variant && $variant->stock >= $itemData['quantity']) {
                                $variant->decrement('stock', $itemData['quantity']);
                            }
                        } else {
                            $product = Product::find($itemData['product_id']);
                            if ($product && $product->stock >= $itemData['quantity']) {
                                $product->decrement('stock', $itemData['quantity']);
                            }
                        }
                        
                        // Create order item
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $itemData['product_id'],
                            'variant_id' => $itemData['variant_id'] ?? null,
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['original_price'],
                            'final_price' => $itemData['final_price'],
                            'discount_type' => $itemData['discount_type'],
                            'discount_value' => $itemData['discount_value'],
                        ]);
                    }
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_method' => 'card',
                        'payment_gateway' => 'stripe',
                        'transaction_id' => $session->payment_intent . '-V' . $vendorId,
                        'amount' => $vendorTotal,
                        'status' => 'completed',
                        'paid_at' => now(),
                        'gateway_response' => json_encode([
                            'session_id' => $sessionId,
                            'payment_intent' => $session->payment_intent,
                            'payment_status' => $session->payment_status,
                            'vendor_id' => $vendorId,
                            'coupon_discount' => $vendorCouponDiscount,
                        ]),
                    ]);$vendor = \App\Models\Vendor::find($vendorId);
if ($vendor) {
    $vendor->increment('total_earnings', $vendorTotal);
    
    \Log::info('Vendor earnings added for card payment', [
        'order_id' => $order->id,
        'vendor_id' => $vendorId,
        'amount_added' => $vendorTotal,
        'payment_method' => 'card',
        'new_total_earnings' => $vendor->fresh()->total_earnings,
        'timestamp' => now()
    ]);
}
                }
                
                // Record coupon usage (only once for main order)
                if (!empty($orderData['coupon_id']) && $orderData['coupon_discount'] > 0 && $firstOrder) {
                    $coupon = Coupon::find($orderData['coupon_id']);
                    
                    if ($coupon) {
                        CouponUsage::create([
                            'coupon_id' => $coupon->id,
                            'user_id' => $user->id,
                            'order_id' => $firstOrder->id,
                            'discount_amount' => $orderData['coupon_discount'],
                        ]);
                        
                        $coupon->increment('usage_count');
                    }
                }
                
                // Clear cart
                $cart = Cart::where('user_id', $user->id)->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)->delete();
                }
                
                // Clear sessions
                session()->forget(['applied_coupon', 'pending_order_data']);
                
                DB::commit();
                
                return redirect()->route('payment.success', ['transaction_id' => $session->payment_intent])
                    ->with('success', 'Payment successful! Your order has been placed.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order creation error: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Stripe Success Error: ' . $e->getMessage());
            return redirect()->route('payment.failed')
                ->with('error', 'Error processing your order: ' . $e->getMessage());
        }
    }
    
    /**
     * Payment success page
     */
    public function success(Request $request)
    {
        $transactionId = $request->get('transaction_id');
        
        if (!$transactionId) {
            return redirect()->route('home')
                ->with('error', 'Invalid payment reference');
        }
        
        $orders = Order::with(['items.product', 'payment', 'vendor'])
            ->where('user_id', Auth::id())
            ->where('transaction_id', $transactionId)
            ->get();
        
        if ($orders->isEmpty()) {
            return redirect()->route('home')
                ->with('error', 'Orders not found');
        }
        
        $totalAmount = $orders->sum('grand_total');
        
        $shippingAddress = [
            'recipient_name' => $orders->first()->recipient_name,
            'phone' => $orders->first()->phone,
            'address_line' => $orders->first()->address_line,
            'city' => $orders->first()->city,
            'state' => $orders->first()->state,
            'postal_code' => $orders->first()->postal_code,
            'country' => $orders->first()->country,
        ];
        
        return view('payment.success', compact('orders', 'totalAmount', 'transactionId', 'shippingAddress'));
    }
    
    /**
     * Payment failed page
     */
    public function failed()
    {
        return view('payment.failed');
    }
}