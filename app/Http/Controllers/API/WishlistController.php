<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->with(['product' => function($query) {
                $query->with(['vendor', 'category', 'images']);
            }])
            ->get();
            
        return response()->json([
            'wishlist' => $wishlist,
            'total' => $wishlist->count()
        ]);
    }
    
    /**
     * Add product to wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $user = $request->user();
        
        // Check if already in wishlist
        $existing = Wishlist::where([
            'user_id' => $user->id,
            'product_id' => $request->product_id
        ])->first();
        
        if ($existing) {
            return response()->json([
                'message' => 'Product already in wishlist'
            ], 422);
        }
        
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id
        ]);
        
        return response()->json([
            'message' => 'Product added to wishlist',
            'wishlist' => $wishlist
        ], 201);
    }
    
    /**
     * Remove product from wishlist
     */
    public function destroy(Request $request, $productId)
    {
        $deleted = Wishlist::where([
            'user_id' => $request->user()->id,
            'product_id' => $productId
        ])->delete();
        
        if (!$deleted) {
            return response()->json([
                'message' => 'Product not found in wishlist'
            ], 404);
        }
        
        return response()->json([
            'message' => 'Product removed from wishlist'
        ]);
    }
    
    /**
     * Toggle wishlist (add if not exists, remove if exists)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $user = $request->user();
        $productId = $request->product_id;
        
        $wishlist = Wishlist::where([
            'user_id' => $user->id,
            'product_id' => $productId
        ])->first();
        
        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'message' => 'Product removed from wishlist',
                'in_wishlist' => false
            ]);
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);
            return response()->json([
                'message' => 'Product added to wishlist',
                'in_wishlist' => true
            ], 201);
        }
    }
}