<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    /**
     * Create Stripe Checkout Session (Redirect Method)
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
                ->with('product')
                ->get();
            
            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Cart is empty');
            }
            
            // Calculate totals
            $subtotal = $cartItems->sum(function($item) {
                return $item->quantity * $item->final_price;
            });
            
            // Get coupon discount
            $couponDiscount = 0;
            $appliedCoupon = session('applied_coupon');
            if ($appliedCoupon) {
                $couponDiscount = $appliedCoupon['discount'];
            }
            
            $tax = ($subtotal - $couponDiscount) * 0.10;
            $total = $subtotal - $couponDiscount + $tax;
            
            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Prepare line items for Stripe
            $lineItems = [];
            foreach ($cartItems as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product->name,
                            'description' => 'Product from ' . config('app.name'),
                        ],
                        'unit_amount' => round($item->final_price * 100), // Convert to cents
                    ],
                    'quantity' => $item->quantity,
                ];
            }
            
            // Add tax as line item
            if ($tax > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Tax (10%)',
                        ],
                        'unit_amount' => round($tax * 100),
                    ],
                    'quantity' => 1,
                ];
            }
            
            // Add discount as line item (negative amount)
            if ($couponDiscount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Coupon Discount',
                        ],
                        'unit_amount' => -round($couponDiscount * 100),
                    ],
                    'quantity' => 1,
                ];
            }
            
            // Store order data in session for later
            session([
                'pending_order_data' => [
                    'address_id' => $request->address_id,
                    'notes' => $request->notes,
                    'cart_items' => $cartItems->toArray(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'coupon_discount' => $couponDiscount,
                    'total' => $total,
                ]
            ]);
            
            // Create Stripe Checkout Session
            $session = StripeSession::create([
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
            ]);
            
            // Redirect to Stripe Checkout
            return redirect($session->url);
            
        } catch (\Exception $e) {
            \Log::error('Stripe Checkout Error: ' . $e->getMessage());
            return redirect()->route('payment.failed')
                ->with('error', 'Payment processing error. Please try again.');
        }
    }
    
    /**
     * Handle successful payment from Stripe Checkout
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
            
            // Retrieve the session
            $session = StripeSession::retrieve($sessionId);
            
            if ($session->payment_status !== 'paid') {
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment was not completed');
            }
            
            // Get order data from session
            $orderData = session('pending_order_data');
            if (!$orderData) {
                return redirect()->route('payment.failed')
                    ->with('error', 'Order data not found');
            }
            
            $user = Auth::user();
            
            DB::beginTransaction();
            
            try {
                // Group items by vendor
                $cartItems = collect($orderData['cart_items']);
                $itemsByVendor = $cartItems->groupBy('product.vendor_id');
                
                $firstOrder = null;
                
                foreach ($itemsByVendor as $vendorId => $items) {
                    // Calculate vendor order total
                    $vendorSubtotal = collect($items)->sum(function($item) {
                        return $item['quantity'] * $item['final_price'];
                    });
                    
                    $vendorCouponDiscount = 0;
                    if ($orderData['coupon_discount'] > 0) {
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
                        'coupon_discount' => $vendorCouponDiscount,
                        'grand_total' => $vendorTotal,
                        'status' => 'pending',
                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'transaction_id' => $session->payment_intent,
                        'shipping_address_id' => $orderData['address_id'],
                        'notes' => $orderData['notes'],
                    ]);
                    
                    if (!$firstOrder) {
                        $firstOrder = $order;
                    }
                    
                    // Create order items
                    foreach ($items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'quantity' => $item['quantity'],
                            'price' => $item['final_price'],
                            'total' => $item['quantity'] * $item['final_price'],
                        ]);
                    }
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_method' => 'stripe',
                        'transaction_id' => $session->payment_intent,
                        'amount' => $vendorTotal,
                        'currency' => 'USD',
                        'status' => 'success',
                        'payment_details' => json_encode([
                            'session_id' => $sessionId,
                            'payment_intent' => $session->payment_intent,
                            'payment_status' => $session->payment_status,
                        ]),
                    ]);
                }
                
                // Clear cart
                $cart = Cart::where('user_id', $user->id)->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)->delete();
                }
                
                // Clear sessions
                session()->forget(['applied_coupon', 'pending_order_data']);
                
                DB::commit();
                
                // Redirect to success page
                return redirect()->route('payment.success', ['order' => $firstOrder->id])
                    ->with('success', 'Payment successful! Your order has been placed.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Order creation error: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Payment success handler error: ' . $e->getMessage());
            return redirect()->route('payment.failed')
                ->with('error', 'Error processing your order. Please contact support.');
        }
    }
    
    /**
     * Payment success page
     */
    public function success($orderId)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
        
        return view('payment.success', compact('order'));
    }
    
    /**
     * Payment failed page
     */
    public function failed()
    {
        return view('payment.failed');
    }
}