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
use App\Models\ProductVariant; // ✅ ADD THIS
use App\Models\Coupon;//new
use App\Models\CouponUsage;//new

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
    
     // Get or create cart (no total_amount column)
     $cart = Cart::firstOrCreate(
        ['user_id' => $user->id]
     );

     // Get cart items with products AND variants (✅ UPDATED)
     $cartItems = CartItem::where('cart_id', $cart->id)
        ->with([
            'product.images',
            'product.vendor',
            'variant' // ✅ ADD THIS LINE - loads variant data
        ])
        ->get()
        ->map(function($item) { // ✅ ADD THIS MAPPING
            // Add image URLs for display
            $item->product->image_urls = $item->product->images->map(
                fn($img) => asset('storage/' . $img->image_path)
            );
            return $item;
        }); // ✅ END OF MAPPING

     // Calculate totals using final_price (price after discount)
     $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });

    $cart = [
        'id' => $cart->id,
        'items' => $cartItems->toArray(),
        'subtotal' => $subtotal,
        'tax' => $subtotal * 0.1, // 10% tax
        'total' => $subtotal + ($subtotal * 0.1),
    ];

    return view('customer.cart', compact('cart'));
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
            return redirect()->back()->with('error', 'Insufficient stock for selected variant.');
        }

        if (!$variant->is_active) {
            return redirect()->back()->with('error', 'Selected variant is not available.');
        }
    } else {
        if ($product->stock < $quantity) {
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
            return redirect()->back()->with('error', 'Cannot add more. Maximum stock available: ' . $maxStock);
        }
        
        $existingItem->quantity = $newQuantity;
        $existingItem->save();
        
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
    
    // ✅ Load cart item with variant relationship
    $cartItem = CartItem::where('cart_id', $cart->id)
        ->where('id', $id)
        ->with('variant') // ✅ NEW: Load variant
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

    // ✅ Check stock availability (variant or product)
    $maxStock = $cartItem->variant ? $cartItem->variant->stock : $cartItem->product->stock;
    
    if ($newQuantity > $maxStock) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot add more. Only ' . $maxStock . ' available in stock.'
        ], 400);
    }

    $cartItem->quantity = $newQuantity;
    $cartItem->save();

    return response()->json(['success' => true]);
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
    
    // Find coupon
    $coupon = Coupon::where('code', $couponCode)->first();
    
    if (!$coupon) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid coupon code.'
        ], 422);
    }
    
    // Check if coupon is valid
    if (!$coupon->isValid()) {
        return response()->json([
            'success' => false,
            'message' => 'This coupon is no longer valid or has expired.'
        ], 422);
    }
    
    // Check if user can use this coupon
    if (!$coupon->canBeUsedBy($user->id)) {
        return response()->json([
            'success' => false,
            'message' => 'You have already used this coupon the maximum number of times.'
        ], 422);
    }
    
    // Get cart to calculate discount
    $cart = Cart::where('user_id', $user->id)->first();
    if (!$cart) {
        return response()->json([
            'success' => false,
            'message' => 'Your cart is empty.'
        ], 422);
    }
    
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->with('product')
        ->get();
    
    if ($cartItems->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Your cart is empty.'
        ], 422);
    }
    
    // Calculate subtotal
    $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });
    
    // Calculate discount
    $discount = $coupon->calculateDiscount($subtotal);
    
    if ($discount == 0) {
        $minPurchase = $coupon->min_purchase ? '$' . number_format($coupon->min_purchase, 2) : '';
        return response()->json([
            'success' => false,
            'message' => $minPurchase ? "Minimum purchase of {$minPurchase} required to use this coupon." : 'This coupon cannot be applied to your cart.'
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
    
    // Calculate new totals
    $tax = $subtotal * 0.10;
    $total = $subtotal - $discount + $tax;
    
    return response()->json([
        'success' => true,
        'message' => 'Coupon applied successfully!',
        'coupon' => [
            'code' => $coupon->code,
            'discount' => $discount,
        ],
        'totals' => [
            'subtotal' => $subtotal,
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
   public function checkout()
{
    $user = Auth::user();
    
    $cart = Cart::where('user_id', $user->id)->first();
    
    if (!$cart || CartItem::where('cart_id', $cart->id)->count() == 0) {
        return redirect()->route('customer.cart')->with('error', 'Your cart is empty');
    }

    $cartItems = CartItem::where('cart_id', $cart->id)
        ->with(['product.images', 'product.vendor', 'variant'])
        ->get();

    // Calculate subtotal
    $subtotal = $cartItems->sum(function($item) {
        return $item->quantity * $item->final_price;
    });
    
    // ✅ Get applied coupon from session
    $appliedCoupon = session('applied_coupon');
    $couponDiscount = 0;
    
    // ✅ Validate coupon is still valid if applied
    if ($appliedCoupon) {
        $coupon = Coupon::find($appliedCoupon['id']);
        if (!$coupon || !$coupon->isValid()) {
            // Coupon expired or no longer valid
            session()->forget('applied_coupon');
            $appliedCoupon = null;
        } else {
            // Recalculate discount based on current subtotal
            $couponDiscount = $coupon->calculateDiscount($subtotal);
            
            // Update session with recalculated discount
            if ($couponDiscount > 0) {
                session(['applied_coupon.discount' => $couponDiscount]);
                $appliedCoupon['discount'] = $couponDiscount;
            } else {
                // Minimum purchase no longer met
                session()->forget('applied_coupon');
                $appliedCoupon = null;
            }
        }
    }

    $tax = $subtotal * 0.1;
    $total = $subtotal - $couponDiscount + $tax;

    $cart = [
        'id' => $cart->id,
        'items' => $cartItems->toArray(),
        'subtotal' => $subtotal,
        'coupon_discount' => $couponDiscount,  // ✅ ADD
        'tax' => $tax,
        'total' => $total,
    ];

    $addresses = UserAddress::where('user_id', $user->id)->get()->toArray();

    return view('customer.checkout', compact('cart', 'addresses', 'appliedCoupon'));
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
        
        // ✅ Get applied coupon from session
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

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'vendor_id' => $vendorId,
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
        
        // ✅ Record coupon usage
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
    
    // ✅ Clear applied coupon from session
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
            'recipient_name' => 'required|string|max:100',  // ✅ Add this
        'phone' => 'required|string|max:20',            // ✅ Add this
        'address_line' => 'required|string',            // ✅ Changed from address_line_1
        'city' => 'required|string',
        'state' => 'required|string',
        'postal_code' => 'required|string',
        'country' => 'required|string',
        ]);

        UserAddress::create([
            'user_id' => Auth::id(),
        'recipient_name' => $request->recipient_name,   // ✅ Add this
        'phone' => $request->phone,                     // ✅ Add this
        'address_line' => $request->address_line,       // ✅ Changed from address_line_1
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
}