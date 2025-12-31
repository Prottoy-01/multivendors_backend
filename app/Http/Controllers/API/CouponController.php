<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Admin: List all coupons
     */
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($coupons);
    }
    
    /**
     * Admin: Create coupon
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed,free_shipping',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'description' => 'nullable|string|max:500',
        ]);
        
        $coupon = Coupon::create([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_purchase' => $request->min_purchase,
            'max_discount' => $request->max_discount,
            'usage_limit' => $request->usage_limit,
            'per_user_limit' => $request->per_user_limit ?? 1,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'description' => $request->description,
            'created_by' => $request->user()->id,
            'is_active' => true,
        ]);
        
        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon
        ], 201);
    }
    
    /**
     * Customer: Validate and apply coupon
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0'
        ]);
        
        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();
            
        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }
        
        // Check validity period
        $now = Carbon::now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon is not yet valid'
            ], 422);
        }
        
        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon has expired'
            ], 422);
        }
        
        // Check usage limit
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon usage limit reached'
            ], 422);
        }
        
        // Check per-user limit
        $userUsageCount = CouponUsage::where([
            'coupon_id' => $coupon->id,
            'user_id' => $request->user()->id
        ])->count();
        
        if ($userUsageCount >= $coupon->per_user_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'You have already used this coupon'
            ], 422);
        }
        
        // Check minimum purchase
        if ($coupon->min_purchase && $request->subtotal < $coupon->min_purchase) {
            return response()->json([
                'valid' => false,
                'message' => "Minimum purchase of {$coupon->min_purchase} required"
            ], 422);
        }
        
        // Calculate discount
        $discount = $this->calculateDiscount($coupon, $request->subtotal);
        
        return response()->json([
            'valid' => true,
            'message' => 'Coupon applied successfully',
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $request->subtotal - $discount
        ]);
    }
    
    /**
     * Calculate discount amount
     */
    private function calculateDiscount($coupon, $subtotal)
    {
        if ($coupon->type === 'percentage') {
            $discount = ($subtotal * $coupon->value) / 100;
            
            // Apply max discount cap if set
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
            
            return round($discount, 2);
        }
        
        if ($coupon->type === 'fixed') {
            return min($coupon->value, $subtotal); // Can't discount more than subtotal
        }
        
        return 0; // free_shipping handled separately
    }
    
    /**
     * Admin: Update coupon
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $request->validate([
            'is_active' => 'sometimes|boolean',
            'usage_limit' => 'sometimes|integer|min:0',
            'valid_until' => 'sometimes|date',
        ]);
        
        $coupon->update($request->only([
            'is_active', 'usage_limit', 'valid_until'
        ]));
        
        return response()->json([
            'message' => 'Coupon updated successfully',
            'coupon' => $coupon
        ]);
    }
    
    /**
     * Admin: Delete coupon
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Only delete if not used
        if ($coupon->usage_count > 0) {
            return response()->json([
                'message' => 'Cannot delete coupon that has been used. Deactivate it instead.'
            ], 422);
        }
        
        $coupon->delete();
        
        return response()->json([
            'message' => 'Coupon deleted successfully'
        ]);
    }
}