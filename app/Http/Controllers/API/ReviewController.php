<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a product
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->with('user:id,name,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($reviews);
    }
    
    /**
     * Create a review (only for verified purchases)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $user = $request->user();
        
        // Verify user purchased this product
        $orderItem = OrderItem::whereHas('order', function($query) use ($user, $request) {
            $query->where('user_id', $user->id)
                  ->where('id', $request->order_id)
                  ->where('status', 'delivered'); // Only allow reviews after delivery
        })->where('product_id', $request->product_id)->first();
        
        if (!$orderItem) {
            return response()->json([
                'message' => 'You can only review products you have purchased and received'
            ], 403);
        }
        
        // Check if already reviewed
        $existing = Review::where([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'order_id' => $request->order_id
        ])->first();
        
        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this product for this order'
            ], 422);
        }
        
        // Create review
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified_purchase' => true,
            'is_approved' => true, // Auto-approve or change to false for moderation
        ]);
        
        // Update product rating metrics
        $this->updateProductRating($request->product_id);
        
        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }
    
    /**
     * Update product average rating
     */
    private function updateProductRating($productId)
    {
        $product = Product::findOrFail($productId);
        
        $stats = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();
            
        $product->update([
            'average_rating' => round($stats->avg_rating, 2),
            'total_reviews' => $stats->total
        ]);
    }
    
    /**
     * Delete own review
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $productId = $review->product_id;
        $review->delete();
        
        // Update product rating
        $this->updateProductRating($productId);
        
        return response()->json(['message' => 'Review deleted successfully']);
    }
    
    /**
     * Admin: Approve/Reject review
     */
    public function moderate(Request $request, $id)
    {
        $request->validate([
            'is_approved' => 'required|boolean'
        ]);
        
        $review = Review::findOrFail($id);
        $review->is_approved = $request->is_approved;
        $review->save();
        
        // Update product rating
        $this->updateProductRating($review->product_id);
        
        return response()->json([
            'message' => 'Review moderated successfully',
            'review' => $review
        ]);
    }
}