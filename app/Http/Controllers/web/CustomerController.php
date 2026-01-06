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

        // Get cart items with products
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->with(['product.images', 'product.vendor'])
            ->get();

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
        $user = Auth::user();
        
        // Get or create cart (no total_amount column)
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id]
        );

        // Get product
        $product = Product::findOrFail($request->product_id);
        
        // Calculate final price (after discount if any)
        $finalPrice = $product->final_price ?? $product->price;
        
        // Check if item already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->quantity += $request->quantity ?? 1;
            $cartItem->save();
        } else {
            // Add new item - MUST include both price AND final_price
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity ?? 1,
                'price' => $product->price,           // Original price
                'final_price' => $finalPrice,         // Price after discount
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    /**
     * Update cart item
     */
    public function updateCart(Request $request, $id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->quantity = $request->quantity;
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
            ->with(['product.images', 'product.vendor'])
            ->get();

        // Calculate totals using final_price
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->final_price;
        });

        $cart = [
            'id' => $cart->id,
            'items' => $cartItems->toArray(),
            'subtotal' => $subtotal,
            'tax' => $subtotal * 0.1,
            'total' => $subtotal + ($subtotal * 0.1),
        ];

        $addresses = UserAddress::where('user_id', $user->id)->get()->toArray();

        return view('customer.checkout', compact('cart', 'addresses'));
    }

    /**
     * Place order
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cash_on_delivery,card,bank_transfer',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $cartItems = CartItem::where('cart_id', $cart->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty');
        }

        // Get address
        $address = UserAddress::findOrFail($request->address_id);

        // Calculate totals using final_price
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->final_price;
        });
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $total,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_address' => $address->address_line_1,
            'shipping_city' => $address->city,
            'shipping_state' => $address->state,
            'shipping_postal_code' => $address->postal_code,
            'shipping_country' => $address->country,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,              // Original price
                'final_price' => $item->final_price,  // Price after discount
            ]);

            // Decrease product stock
            $product = $item->product;
            $product->stock -= $item->quantity;
            $product->save();
        }

        // Clear cart (don't update total_amount since it doesn't exist)
        CartItem::where('cart_id', $cart->id)->delete();

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

    /**
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
            'label' => 'required|string|max:50',
            'address_line_1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
        ]);

        UserAddress::create([
            'user_id' => Auth::id(),
            'label' => $request->label,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
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
    public function storeReview(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Review submitted successfully!');
    }
}