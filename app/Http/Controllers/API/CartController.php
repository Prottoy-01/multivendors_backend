<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    // Get or create cart for user
    private function getUserCart($userId)
    {
        return Cart::firstOrCreate([
            'user_id' => $userId
        ]);
    }

    //  View Cart
    public function index(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Cart is empty'], 200);
        }

        return response()->json($cart);
    }

    //  Add to Cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $this->getUserCart($request->user()->id);
        $product = Product::findOrFail($request->product_id);

        // Store prices at the moment of adding to cart
        $originalPrice = $product->price;
        $finalPrice = $product->final_price;

        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $originalPrice,   // Original price
                'final_price' => $finalPrice, // Price with offer applied
            ]);
        }

        return response()->json(['message' => 'Product added to cart']);
    }

    //  Update Cart Item Quantity
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::findOrFail($id);
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($item->cart_id !== $cart->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['message' => 'Cart updated']);
    }

    //  Remove Item from Cart
    public function remove(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($item->cart_id !== $cart->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}
