<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Customer dashboard
     */
    public function dashboard()
    {
        $ordersResponse = $this->api->getOrders();
        $orders = $ordersResponse['data'] ?? [];
        
        $wishlistResponse = $this->api->getWishlist();
        $wishlistCount = count($wishlistResponse['data'] ?? []);
        
        $cartResponse = $this->api->getCart();
        $cartCount = count($cartResponse['items'] ?? []);

        return view('customer.dashboard', compact('orders', 'wishlistCount', 'cartCount'));
    }

    /**
     * Orders listing
     */
    public function orders()
    {
        $response = $this->api->getOrders();
        $orders = $response['data'] ?? [];

        return view('customer.orders', compact('orders'));
    }

    /**
     * Order detail
     */
    public function orderDetail($id)
    {
        $response = $this->api->getOrders();
        $orders = $response['data'] ?? [];
        $order = collect($orders)->firstWhere('id', $id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return view('customer.order-detail', compact('order'));
    }

    /**
     * Wishlist
     */
    public function wishlist()
    {
        $response = $this->api->getWishlist();
        $wishlist = $response['data'] ?? [];

        return view('customer.wishlist', compact('wishlist'));
    }

    /**
     * Toggle wishlist
     */
    public function toggleWishlist(Request $request)
    {
        $response = $this->api->toggleWishlist($request->product_id);

        if (isset($response['message'])) {
            return response()->json(['success' => true, 'message' => $response['message']]);
        }

        return response()->json(['success' => false, 'message' => 'Failed to update wishlist'], 400);
    }

    /**
     * Shopping cart
     */
    public function cart()
    {
        $response = $this->api->getCart();
        $cart = $response;

        return view('customer.cart', compact('cart'));
    }

    /**
     * Add to cart
     */
    public function addToCart(Request $request)
    {
        $response = $this->api->addToCart([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity ?? 1,
        ]);

        if (isset($response['message'])) {
            return redirect()->back()->with('success', 'Product added to cart!');
        }

        return redirect()->back()->with('error', 'Failed to add product to cart');
    }

    /**
     * Update cart item
     */
    public function updateCart(Request $request, $id)
    {
        $response = $this->api->updateCartItem($id, [
            'quantity' => $request->quantity,
        ]);

        return response()->json(['success' => isset($response['message'])]);
    }

    /**
     * Remove from cart
     */
    public function removeFromCart($id)
    {
        $response = $this->api->removeFromCart($id);

        if (isset($response['message'])) {
            return redirect()->back()->with('success', 'Item removed from cart');
        }

        return redirect()->back()->with('error', 'Failed to remove item');
    }

    /**
     * Checkout page
     */
    public function checkout()
    {
        $cartResponse = $this->api->getCart();
        $cart = $cartResponse;

        if (empty($cart['items'])) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty');
        }

        $addressesResponse = $this->api->getAddresses();
        $addresses = $addressesResponse['data'] ?? [];

        return view('customer.checkout', compact('cart', 'addresses'));
    }

    /**
     * Place order
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'address_id' => 'required',
            'payment_method' => 'required',
        ]);

        $response = $this->api->placeOrder([
            'address_id' => $request->address_id,
            'payment_method' => $request->payment_method,
            'coupon_code' => $request->coupon_code,
            'notes' => $request->notes,
        ]);

        if (isset($response['order'])) {
            return redirect()->route('customer.orders')->with('success', 'Order placed successfully!');
        }

        return back()->with('error', $response['message'] ?? 'Failed to place order');
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $response = $this->api->getProfile();
        $user = $response['data'] ?? [];

        $addressesResponse = $this->api->getAddresses();
        $addresses = $addressesResponse['data'] ?? [];

        return view('customer.profile', compact('user', 'addresses'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $data = $request->only(['name', 'phone', 'bio']);

        $response = $this->api->updateProfile($data);

        if (isset($response['data'])) {
            session()->put('user', $response['data']);
            return redirect()->back()->with('success', 'Profile updated successfully!');
        }

        return back()->with('error', 'Failed to update profile');
    }

    /**
     * Addresses page
     */
    public function addresses()
    {
        $response = $this->api->getAddresses();
        $addresses = $response['data'] ?? [];

        return view('customer.addresses', compact('addresses'));
    }

    /**
     * Store new address
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string',
            'address_line_1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
        ]);

        $response = $this->api->createAddress($request->all());

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Address added successfully!');
        }

        return back()->with('error', 'Failed to add address');
    }

    /**
     * Store review
     */
    public function storeReview(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $response = $this->api->createReview($request->all());

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Review submitted successfully!');
        }

        return back()->with('error', 'Failed to submit review');
    }
}