<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\UserAddress;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\OrderCancellation;
use App\Models\Payment;
use App\Models\Vendor; // ⭐ ADDED - Required for vendor earnings
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Customer dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get orders directly from database
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        // Get wishlist count
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        
        // Get cart count
        $cart = Cart::where('user_id', $user->id)->first();
        $cartCount = $cart ? CartItem::where('cart_id', $cart->id)->count() : 0;

        return view('customer.dashboard', compact('orders', 'wishlistCount', 'cartCount'));
    }

    /**
     * Orders listing
     */
    public function orders()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product.images', 'items.product.vendor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return view('customer.orders', compact('orders'));
    }

    /**
     * Order detail
     */
    public function orderDetail($id)
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['items.product.images', 'items.product.vendor'])
            ->firstOrFail()
            ->toArray();

        return view('customer.order-detail', compact('order'));
    }

    /**
     * Wishlist
     */
    public function wishlist()
    {
        $user = Auth::user();
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->with(['product.images', 'product.vendor', 'product.category'])
            ->get()
            ->map(function($item) {
                return $item->product->toArray();
            })
            ->toArray();

        return view('customer.wishlist', compact('wishlist'));
    }

    /**
     * Toggle wishlist
     */
    public function toggleWishlist(Request $request)
    {
        $user = Auth::user();
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
            ]);
            $message = 'Added to wishlist';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Shopping cart
     */
   public function cart()
{
    $user = Auth::user();

    $cart = Cart::firstOrCreate(
        ['user_id' => $user->id]
    );

    // ✅ UPDATED: Load category for coupon checking
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->with([
            'product.images',
            'product.vendor',
            'product.category', // ✅ NEW
            'variant'
        ])
        ->get()
        ->map(function($item) {
            $item->product->image_urls = $item->product->images->map(
                fn($img) => asset('storage/' . $img->image_path)
            );
            return $item;
        });

    $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });

    // ✅ NEW: Get available coupons
    $availableCoupons = $this->getAvailableCoupons($cartItems, $user->id);

    $cart = [
        'id' => $cart->id,
        'items' => $cartItems->toArray(),
        'subtotal' => $subtotal,
        'tax' => $subtotal * 0.1,
        'total' => $subtotal + ($subtotal * 0.1),
    ];

    return view('customer.cart', compact('cart', 'availableCoupons'));
}

/**
 * Get cart item count for AJAX requests
 */
public function cartCount()
{
    $user = Auth::user();
    $cart = Cart::where('user_id', $user->id)->first();
    
    if (!$cart) {
        return response()->json(['count' => 0]);
    }
    
    $count = CartItem::where('cart_id', $cart->id)->sum('quantity');
    
    return response()->json(['count' => $count]);
}


/**
 * Get available coupons for cart items
 */
private function getAvailableCoupons($cartItems, $userId)
{
    if ($cartItems->isEmpty()) {
        return [];
    }

    // Get category IDs from cart items
    $cartCategoryIds = $cartItems->pluck('product.category_id')->unique()->filter()->values();

    // Get valid coupons
    $coupons = Coupon::with('categories')
        ->where('is_active', true)
        ->where(function($q) {
            $now = now();
            $q->where(function($q2) use ($now) {
                $q2->whereNull('valid_from')
                   ->orWhere('valid_from', '<=', $now);
            })
            ->where(function($q2) use ($now) {
                $q2->whereNull('valid_until')
                   ->orWhere('valid_until', '>=', $now);
            });
        })
        ->where(function($q) {
            $q->whereNull('usage_limit')
              ->orWhereColumn('usage_count', '<', 'usage_limit');
        })
        ->get()
        ->filter(function($coupon) use ($userId, $cartCategoryIds) {
            // Check per-user limit
            $userUsageCount = CouponUsage::where([
                'coupon_id' => $coupon->id,
                'user_id' => $userId
            ])->count();
            
            if ($userUsageCount >= $coupon->per_user_limit) {
                return false;
            }

            // Check if coupon applies to cart items
            if ($coupon->applies_to_all) {
                return true;
            }

            // Check if any cart category matches coupon categories
            $couponCategoryIds = $coupon->categories->pluck('id');
            return $couponCategoryIds->intersect($cartCategoryIds)->isNotEmpty();
        })
        ->map(function($coupon) {
            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'description' => $coupon->description,
                'applies_to_all' => $coupon->applies_to_all,
                'categories' => $coupon->categories->pluck('name')->toArray(),
                'min_purchase' => $coupon->min_purchase,
                'valid_until' => $coupon->valid_until?->format('M d, Y'),
            ];
        })
        ->values()
        ->toArray();

    return $coupons;
}

    /**
     * Add to cart
     */
   public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'variant_id' => 'nullable|exists:product_variants,id',
        'quantity' => 'nullable|integer|min:1',
    ]);

    $quantity = $request->quantity ?? 1;

    $product = Product::findOrFail($request->product_id);
    $variant = null;

    if ($request->variant_id) {
        $variant = ProductVariant::where('id', $request->variant_id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        if ($variant->stock < $quantity) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for selected variant.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Insufficient stock for selected variant.');
        }

        if (!$variant->is_active) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected variant is not available.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Selected variant is not available.');
        }
    } else {
        if ($product->stock < $quantity) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Insufficient stock.');
        }
    }

    $price = $variant ? $variant->price : $product->price;
    $finalPrice = $price;

    if ($product->has_offer && $product->discount_value) {
        if ($product->discount_type === 'percentage') {
            $finalPrice = $price - ($price * $product->discount_value / 100);
        } else {
            $finalPrice = $price - $product->discount_value;
        }
    }

    $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

    $existingItem = CartItem::where('cart_id', $cart->id)
        ->where('product_id', $product->id)
        ->where('variant_id', $request->variant_id)
        ->first();

    if ($existingItem) {
        // $existingItem->quantity += $quantity;
        // $existingItem->save();

        // return redirect()->back()->with('success', 'Cart updated!');
        // Update quantity
        $newQuantity = $existingItem->quantity + $request->quantity;
        
        // Check stock again
        $maxStock = $variant ? $variant->stock : $product->stock;
        if ($newQuantity > $maxStock) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more. Maximum stock available: ' . $maxStock
                ], 400);
            }
            return redirect()->back()->with('error', 'Cannot add more. Maximum stock available: ' . $maxStock);
        }
        
        $existingItem->quantity = $newQuantity;
        $existingItem->save();
        
        if ($request->expectsJson() || $request->ajax()) {
            $cartItems = CartItem::where('cart_id', $cart->id)->get();
            $count = $cartItems->sum('quantity');
            return response()->json([
                'success' => true,
                'message' => 'Cart updated! Quantity increased.',
                'cart_count' => $count
            ]);
        }
        return redirect()->back()->with('success', 'Cart updated! Quantity increased.');
    }

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'variant_id' => $request->variant_id,
        'quantity' => $quantity,
        'price' => $price,
        'final_price' => $finalPrice,
    ]);

    if ($request->expectsJson() || $request->ajax()) {
        $cartItems = CartItem::where('cart_id', $cart->id)->get();
        $count = $cartItems->sum('quantity');
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart!',
            'cart_count' => $count
        ]);
    }
    return redirect()->back()->with('success', 'Product added to cart!');
}

    /**
     * Update cart item
     */
    // public function updateCart(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
    //     $cartItem = CartItem::where('cart_id', $cart->id)
    //         ->where('id', $id)
    //         ->firstOrFail();

    //     $cartItem->quantity = $request->quantity;
    //     $cartItem->save();

    //     return response()->json(['success' => true]);
    // }
    public function updateCart(Request $request, $id)
{
    $user = Auth::user();
    $cart = Cart::where('user_id', $user->id)->firstOrFail();
    
    //  Load cart item with variant relationship
    $cartItem = CartItem::where('cart_id', $cart->id)
        ->where('id', $id)
        ->with('variant') //  NEW: Load variant
        ->firstOrFail();

    $action = $request->action;
    $newQuantity = $cartItem->quantity;

    if ($action === 'increase') {
        $newQuantity++;
    } elseif ($action === 'decrease') {
        $newQuantity = max(1, $newQuantity - 1);
    } elseif ($request->has('quantity')) {
        $newQuantity = max(1, (int)$request->quantity);
    }

    // Check stock availability (variant or product)
    $maxStock = $cartItem->variant ? $cartItem->variant->stock : $cartItem->product->stock;
    
    if ($newQuantity > $maxStock) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot add more. Only ' . $maxStock . ' available in stock.'
        ], 400);
    }

    $cartItem->quantity = $newQuantity;
    $cartItem->save();

    // Calculate totals for AJAX response
    $cartItems = CartItem::where('cart_id', $cart->id)->get();
    $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });
    $tax = $subtotal * 0.1;
    $total = $subtotal + $tax;

    return response()->json([
        'success' => true,
        'message' => 'Cart updated',
        'item' => [
            'id' => $cartItem->id,
            'quantity' => $cartItem->quantity,
            'price' => $cartItem->final_price
        ],
        'cart' => [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]
    ]);
}
    /**
     * Remove from cart
     */
    public function removeFromCart($id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->delete();

        // If AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            // Recalculate totals after deletion
            $cartItems = CartItem::where('cart_id', $cart->id)->get();
            $subtotal = $cartItems->sum(function($item) {
                return $item->quantity * $item->final_price;
            });
            $tax = $subtotal * 0.1;
            $total = $subtotal + $tax;

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => [
                    'items_count' => $cartItems->count(),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart');
    }

  // apply copun
  public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $user = Auth::user();
        $couponCode = strtoupper(trim($request->coupon_code));
        
        // Load coupon with categories
        $coupon = Coupon::with('categories')->whereRaw('UPPER(code) = ?', [$couponCode])->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.'
            ], 422);
        }
        
        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is no longer valid or has expired.'
            ], 422);
        }
        
        if (!$coupon->canBeUsedBy($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon the maximum number of times.'
            ], 422);
        }
        
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 422);
        }
        
        // ✅ Load products with categories
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->with('product.category')
            ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 422);
        }
        
        // ✅ NEW: Check if coupon applies to cart items
        $applicableAmount = 0;
        $applicableCount = 0;
        
        if ($coupon->applies_to_all) {
            // Applies to all products - OLD BEHAVIOR
            $applicableAmount = $cartItems->sum(function($item) {
                return $item->quantity * $item->final_price;
            });
            $applicableCount = $cartItems->count();
        } else {
            // Check categories - NEW BEHAVIOR
            $couponCategoryIds = $coupon->categories->pluck('id')->toArray();
            
            foreach ($cartItems as $item) {
                if (in_array($item->product->category_id, $couponCategoryIds)) {
                    $applicableAmount += $item->quantity * $item->final_price;
                    $applicableCount++;
                }
            }
            
            if ($applicableCount == 0) {
                $categoryNames = $coupon->categories->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "This coupon only applies to: {$categoryNames}"
                ], 422);
            }
        }
        
        // Calculate discount on applicable amount
        $discount = $coupon->calculateDiscount($applicableAmount);
        
        if ($discount == 0) {
            $minPurchase = $coupon->min_purchase ? '$' . number_format($coupon->min_purchase, 2) : '';
            return response()->json([
                'success' => false,
                'message' => $minPurchase ? "Minimum purchase of {$minPurchase} required for applicable items." : 'This coupon cannot be applied to your cart.'
            ], 422);
        }
        
        // Store coupon in session
        session([
            'applied_coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'discount' => $discount,
            ]
        ]);
        
        // Calculate totals
        $fullSubtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->final_price;
        });
        $tax = $fullSubtotal * 0.10;
        $total = $fullSubtotal - $discount + $tax;
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => $discount,
            ],
            'totals' => [
                'subtotal' => $fullSubtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ]
        ]);
    }


/**
 * Remove applied coupon
 */
public function removeCoupon()
{
    session()->forget('applied_coupon');
    
    return response()->json([
        'success' => true,
        'message' => 'Coupon removed.'
    ]);
}


    /**
     * Checkout page
     */
/**
 * Checkout page - UPDATED with available coupons
 */
public function checkout()
{
    $user = Auth::user();
    
    $cart = Cart::where('user_id', $user->id)->first();
    
    if (!$cart || CartItem::where('cart_id', $cart->id)->count() == 0) {
        return redirect()->route('customer.cart')->with('error', 'Your cart is empty');
    }

    // ✅ UPDATED: Load with category for coupon checking
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->with(['product.images', 'product.vendor', 'product.category', 'variant']) // ✅ Added category
        ->get();

    // Calculate subtotal
    $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });
    
    // ✅ NEW: Get available coupons for checkout page
    $availableCoupons = $this->getAvailableCoupons($cartItems, $user->id);
    
    // Get applied coupon from session
    $appliedCoupon = session('applied_coupon');
    $couponDiscount = 0;
    
    // Validate coupon is still valid if applied
    if ($appliedCoupon) {
        $coupon = Coupon::find($appliedCoupon['id']);
        if (!$coupon || !$coupon->isValid()) {
            // Coupon expired or no longer valid
            session()->forget('applied_coupon');
            $appliedCoupon = null;
        } else {
            // ✅ UPDATED: Use the discount from session (already calculated correctly)
            $couponDiscount = $appliedCoupon['discount'];
        }
    }

    $tax = $subtotal * 0.1;
    $total = $subtotal - $couponDiscount + $tax;

    $cart = [
        'id' => $cart->id,
        'items' => $cartItems->toArray(),
        'subtotal' => $subtotal,
        'coupon_discount' => $couponDiscount,
        'tax' => $tax,
        'total' => $total,
    ];

    $addresses = UserAddress::where('user_id', $user->id)->get()->toArray();

    // ✅ UPDATED: Pass availableCoupons to view
    return view('customer.checkout', compact('cart', 'addresses', 'appliedCoupon', 'availableCoupons'));
}



/**
 * Place order with variant support
 */
public function placeOrder(Request $request)
{
    $request->validate([
        'address_id' => 'required|exists:user_addresses,id',
        'payment_method' => 'required|in:cash_on_delivery,card,bank_transfer',
    ]);

    $user = Auth::user();
    $cart = Cart::where('user_id', $user->id)->firstOrFail();
    
    // Load cart items with variant relationship
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->with(['product', 'variant'])
        ->get();

    if ($cartItems->isEmpty()) {
        return back()->with('error', 'Your cart is empty');
    }

    // Validate stock for each item (variant or product)
    foreach ($cartItems as $item) {
        $availableStock = $item->variant ? $item->variant->stock : $item->product->stock;
        
        if ($availableStock < $item->quantity) {
            $itemName = $item->product->name;
            if ($item->variant) {
                $itemName .= ' (' . $item->variant->name . ')';
            }
            return back()->with('error', "Insufficient stock for {$itemName}. Only {$availableStock} available.");
        }
    }

    $address = UserAddress::findOrFail($request->address_id);

    // Group by vendor
    $itemsByVendor = $cartItems->groupBy(function($item) {
        return $item->product->vendor_id;
    });

    foreach ($itemsByVendor as $vendorId => $vendorItems) {
        // Calculate amounts
        $totalAmount = $vendorItems->sum(function($item) {
            return $item->quantity * $item->final_price;
        });
        
        // Get applied coupon from session
        $appliedCoupon = session('applied_coupon');
        $coupon = null;
        $couponDiscount = 0;

        if ($appliedCoupon) {
            $coupon = Coupon::find($appliedCoupon['id']);
            if ($coupon && $coupon->isValid() && $coupon->canBeUsedBy($user->id)) {
                $couponDiscount = $coupon->calculateDiscount($totalAmount);
            } else {
                session()->forget('applied_coupon');
            }
        }
        
        $discountTotal = 0;
        $taxAmount = $totalAmount * 0.10;
        $shippingCost = 0;
        $grandTotal = $totalAmount - $couponDiscount + $taxAmount + $shippingCost;

        // ✅ FIXED: Create order with order_number
        $order = Order::create([
            'user_id' => $user->id,
            'vendor_id' => $vendorId,
            'order_number' => 'ORD-' . strtoupper(uniqid()),  // ✅ ADDED THIS LINE
            'total_amount' => $totalAmount,
            'discount_total' => $discountTotal,
            'coupon_id' => $coupon ? $coupon->id : null,
            'coupon_discount' => $couponDiscount,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'grand_total' => $grandTotal,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'address_line' => $address->address_line,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
        ]);

        // Create order items with variant support
        foreach ($vendorItems as $item) {
            $variantDetails = null;
            if ($item->variant) {
                $variantDetails = [
                    'name' => $item->variant->name,
                    'sku' => $item->variant->sku,
                    'attributes' => $item->variant->attributes,
                ];
            }
            
            $order->items()->create([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'variant_details' => $variantDetails,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'final_price' => $item->final_price,
            ]);

            // Reduce stock (variant or product)
            if ($item->variant) {
                $item->variant->stock -= $item->quantity;
                $item->variant->save();
            } else {
                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }
        }

        // ⭐ REMOVED: Vendor earnings should NOT be added here
        // Earnings are added when order status is changed to "shipped"
        // See VendorController::updateOrderStatus() method
        
        // Record coupon usage
        if ($coupon) {
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_amount' => $couponDiscount,
            ]);
            
            $coupon->incrementUsage();
        }
    }

    // Clear cart
    CartItem::where('cart_id', $cart->id)->delete();
    
    // Clear applied coupon from session
    session()->forget('applied_coupon');

    return redirect()->route('customer.orders')->with('success', 'Order placed successfully!');
}


    /**
     * Profile page
     */
    public function profile()
    {
        $user = Auth::user()->toArray();
        $addresses = UserAddress::where('user_id', Auth::id())->get()->toArray();

        return view('customer.profile', compact('user', 'addresses'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $data = $request->only(['name', 'phone', 'bio']);
        $user->update($data);

        session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /*
     * Addresses page
     */
    public function addresses()
    {
        $addresses = UserAddress::where('user_id', Auth::id())->get()->toArray();

        return view('customer.addresses', compact('addresses'));
    }

    /**
     * Store new address
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:100',  //  Add this
        'phone' => 'required|string|max:20',            //  Add this
        'address_line' => 'required|string',            //  Changed from address_line_1
        'city' => 'required|string',
        'state' => 'required|string',
        'postal_code' => 'required|string',
        'country' => 'required|string',
        ]);

        UserAddress::create([
            'user_id' => Auth::id(),
        'recipient_name' => $request->recipient_name,   //  Add this
        'phone' => $request->phone,                     //  Add this
        'address_line' => $request->address_line,       //  Changed from address_line_1
        'city' => $request->city,
        'state' => $request->state,
        'postal_code' => $request->postal_code,
        'country' => $request->country,
        'is_default' => $request->is_default ?? false,
        ]);

        return redirect()->back()->with('success', 'Address added successfully!');
    }

    /**
     * Store review
     */
    /**
 * Store review
 */
public function storeReview(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'required|string|min:10',
    ]);

    $user = Auth::user();

    // Check if user has already reviewed this product
    $existingReview = Review::where('user_id', $user->id)
        ->where('product_id', $request->product_id)
        ->first();

    if ($existingReview) {
        return redirect()->back()->with('error', 'You have already reviewed this product!');
    }

    // Optional: Check if user actually bought this product
    $hasPurchased = Order::where('user_id', $user->id)
        ->whereHas('items', function($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })
        ->where('status', 'delivered')
        ->exists();

    if (!$hasPurchased) {
        return redirect()->back()->with('error', 'You can only review products you have purchased!');
    }

    // Create review
    Review::create([
        'user_id' => $user->id,
        'product_id' => $request->product_id,
        'rating' => $request->rating,
        'order_id' => $request->order_id, //add this
        'comment' => $request->comment,
        'is_approved' => true, // Auto-approve or set to false for admin approval
    ]);

    // Update product rating (optional - calculate average)
    $product = Product::find($request->product_id);
    $avgRating = Review::where('product_id', $request->product_id)
        ->where('is_approved', true)
        ->avg('rating');
    $reviewCount = Review::where('product_id', $request->product_id)
        ->where('is_approved', true)
        ->count();
    
    $product->update([
        'avg_rating' => round($avgRating, 1),
        'review_count' => $reviewCount,
    ]);

    return redirect()->back()->with('success', 'Thank you for your review!');
}


/**
 * Cancel Order with Partial Refund System + Vendor Earnings Adjustment
 * REPLACE THE EXISTING cancelOrder() METHOD IN CustomerController
 */
public function cancelOrder(Request $request, $id)
{
    $request->validate([
        'cancellation_reason' => 'nullable|string|max:500',
    ]);

    $user = Auth::user();
    
    // Get order with all relationships
    $order = Order::where('user_id', $user->id)
        ->where('id', $id)
        ->with(['payment', 'vendor'])
        ->firstOrFail();

    // Check if order can be cancelled
    if (!$order->canBeCancelledByCustomer()) {
        return back()->with('error', 'This order cannot be cancelled at this stage.');
    }

    // Check if already cancelled
    if ($order->isCancelled()) {
        return back()->with('error', 'This order is already cancelled.');
    }

    DB::beginTransaction();
    
    try {
        // Calculate refund based on order status
        $refundPercentage = $order->getRefundPercentage();
        $refundAmount = $order->calculateRefundAmount();
        $vendorRetention = $order->grand_total - $refundAmount;

        // Save current status before cancelling
        $statusAtCancellation = $order->status;

        // Create cancellation record
        $cancellation = OrderCancellation::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'cancelled_by' => 'customer',
            'cancellation_reason' => $request->cancellation_reason,
            'order_status_at_cancellation' => $statusAtCancellation,
            'original_amount' => $order->grand_total,
            'refund_amount' => $refundAmount,
            'refund_percentage' => $refundPercentage,
            'vendor_retention' => $vendorRetention,
            'refund_status' => OrderCancellation::REFUND_PENDING,
        ]);

        // Update order status to cancelled
        $order->status = Order::STATUS_CANCELLED;
        $order->save();

        // ✅ NEW: Adjust Vendor Earnings
        // Vendor loses the refunded amount from their total earnings
        if ($refundAmount > 0 && $order->vendor) {
            // Deduct the refund amount from vendor's total earnings
            // Vendor keeps only the retention amount
            $order->vendor->total_earnings -= $refundAmount;
            $order->vendor->save();
        }

        // Process refund to customer
        if ($refundAmount > 0) {
            // Add refund amount to customer's wallet
            $user->wallet_balance += $refundAmount;
            $user->save();

            // Update cancellation record
            $cancellation->refund_status = OrderCancellation::REFUND_COMPLETED;
            $cancellation->refund_processed_at = now();
            $cancellation->save();

            // Update payment status if exists
            if ($order->payment) {
                $order->payment->update([
                    'status' => Payment::STATUS_REFUNDED,
                ]);
            }
        }

        // Restore stock for cancelled items
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                // Restore variant stock
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $variant->increment('stock', $item->quantity);
                }
            } else {
                // Restore product stock
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        DB::commit();

        // Prepare success message
        $message = 'Order cancelled successfully. ';
        
        if ($refundAmount > 0) {
            if ($refundPercentage == 100) {
                $message .= "Full refund of $" . number_format($refundAmount, 2) . " has been added to your wallet.";
            } else {
                $message .= "Partial refund of $" . number_format($refundAmount, 2) . " ({$refundPercentage}%) has been added to your wallet. ";
                $message .= "The vendor retains $" . number_format($vendorRetention, 2) . " as the order was already shipped.";
            }
        }

        return redirect()->route('customer.orders')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order Cancellation Error: ' . $e->getMessage());
        
        return back()->with('error', 'Failed to cancel order. Please try again or contact support.');
    }
}

/**
 * Show Customer Wallet
 */
public function wallet()
{
    $user = Auth::user();
    
    // Get all refunds (cancelled orders where user got refund)
    $refunds = OrderCancellation::where('user_id', $user->id)
        ->where('refund_status', OrderCancellation::REFUND_COMPLETED)
        ->with('order')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('customer.wallet', compact('refunds'));
}}