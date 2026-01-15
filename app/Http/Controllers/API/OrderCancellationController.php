<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\User;
use App\Models\Vendor;
use DB;
use Illuminate\Support\Facades\Log;

class OrderCancellationController extends Controller
{
    /**
     * Customer: Cancel Order
     * 
     * Business Rules:
     * - If order status is 'pending', 'paid', or 'processing': 100% refund to customer wallet
     * - If order status is 'shipped': 40% refund to customer wallet, 60% retained by vendor
     * - Cannot cancel if status is 'delivered' or already 'cancelled'
     * - Refund goes to customer's wallet balance
     * - Vendor's total_earnings is adjusted accordingly
     */
    public function cancelOrder(Request $request, $orderId)
    {
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500'
        ]);

        $user = $request->user();

        // Find the order
        $order = Order::with(['vendor', 'items.product'])->findOrFail($orderId);

        // Verify order belongs to the user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        // Check if order is already cancelled
        if ($order->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been cancelled'
            ], 400);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelledByCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled. Orders can only be cancelled before delivery.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Get the original order amount (use grand_total if available, otherwise total_amount)
            $originalAmount = $order->grand_total ?? $order->total_amount;
            
            // Calculate refund based on order status
            $refundPercentage = $order->getRefundPercentage();
            $refundAmount = round(($originalAmount * $refundPercentage) / 100, 2);
            $vendorRetention = round($originalAmount - $refundAmount, 2);

            // Store current status before cancellation
            $statusAtCancellation = $order->status;

            // Create cancellation record
            $cancellation = OrderCancellation::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'cancelled_by' => 'customer',
                'cancellation_reason' => $request->cancellation_reason ?? 'Customer requested cancellation',
                'order_status_at_cancellation' => $statusAtCancellation,
                'original_amount' => $originalAmount,
                'refund_amount' => $refundAmount,
                'refund_percentage' => $refundPercentage,
                'vendor_retention' => $vendorRetention,
                'refund_status' => OrderCancellation::REFUND_PENDING,
            ]);

            // Update order status to cancelled
            $order->update([
                'status' => Order::STATUS_CANCELLED
            ]);

            // Process refund to customer's wallet
            $customer = User::findOrFail($user->id);
            $customer->increment('wallet_balance', $refundAmount);

            // Update vendor's total_earnings
            // If order was shipped, vendor keeps 60% (vendor_retention)
            // If order was not shipped, vendor loses the full amount
            $vendor = Vendor::findOrFail($order->vendor_id);
            
            if ($statusAtCancellation === Order::STATUS_SHIPPED) {
                // Vendor was already credited when order was marked as shipped
                // Now we need to deduct the refund amount (40% of original)
                $vendor->decrement('total_earnings', $refundAmount);
            } else {
                // Order was not shipped yet, vendor should not have been credited
                // But if they were credited on order placement, we need to deduct full amount
                // Assuming vendor gets credited on 'delivered' status only, no action needed here
                // However, to be safe, we'll deduct the refund amount
                $vendor->decrement('total_earnings', max(0, $refundAmount));
            }

            // Restore product stock for all items in the order
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Mark refund as completed
            $cancellation->update([
                'refund_status' => OrderCancellation::REFUND_COMPLETED,
                'refund_processed_at' => now(),
            ]);

            DB::commit();

            // Log the cancellation
            Log::info('Order cancelled successfully', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status_at_cancellation' => $statusAtCancellation,
                'refund_percentage' => $refundPercentage,
                'refund_amount' => $refundAmount,
                'vendor_retention' => $vendorRetention,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'refund_amount' => $refundAmount,
                    'refund_percentage' => $refundPercentage,
                    'vendor_retention' => $vendorRetention,
                    'refund_status' => 'completed',
                    'wallet_balance' => $customer->wallet_balance,
                    'message' => $refundPercentage < 100 
                        ? "Order was shipped. You will receive {$refundPercentage}% refund (৳{$refundAmount}) to your wallet."
                        : "You will receive full refund (৳{$refundAmount}) to your wallet."
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order cancellation failed', [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Order cancellation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order cancellation details
     */
    public function getCancellationDetails(Request $request, $orderId)
    {
        $user = $request->user();
        
        $order = Order::with('cancellation')->findOrFail($orderId);

        // Verify order belongs to the user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        if (!$order->cancellation) {
            return response()->json([
                'success' => false,
                'message' => 'This order has not been cancelled'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->cancellation
        ], 200);
    }

    /**
     * Check if order can be cancelled and show refund preview
     */
    public function checkCancellationEligibility(Request $request, $orderId)
    {
        $user = $request->user();
        
        $order = Order::findOrFail($orderId);

        // Verify order belongs to the user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        // Check if already cancelled
        if ($order->isCancelled()) {
            return response()->json([
                'success' => false,
                'can_cancel' => false,
                'message' => 'This order has already been cancelled'
            ], 200);
        }

        // Check eligibility
        $canCancel = $order->canBeCancelledByCustomer();
        $refundPercentage = $order->getRefundPercentage();
        $refundAmount = $order->calculateRefundAmount();
        $originalAmount = $order->grand_total ?? $order->total_amount;

        return response()->json([
            'success' => true,
            'can_cancel' => $canCancel,
            'data' => [
                'order_status' => $order->status,
                'original_amount' => $originalAmount,
                'refund_percentage' => $refundPercentage,
                'refund_amount' => $refundAmount,
                'vendor_retention' => round($originalAmount - $refundAmount, 2),
                'message' => $this->getCancellationMessage($order->status, $refundPercentage)
            ]
        ], 200);
    }

    /**
     * Helper: Get user-friendly cancellation message
     */
    private function getCancellationMessage($status, $refundPercentage)
    {
        if ($refundPercentage == 0) {
            return 'This order cannot be cancelled as it has already been delivered.';
        }

        if ($refundPercentage == 100) {
            return 'You will receive a full refund to your wallet if you cancel this order.';
        }

        if ($refundPercentage == 40) {
            return 'This order has been shipped. You will receive a 40% refund to your wallet if you cancel. The vendor will retain 60% as the product has already been dispatched.';
        }

        return "You will receive a {$refundPercentage}% refund to your wallet if you cancel this order.";
    }

    /**
     * Vendor: View cancellations for their orders
     */
    public function vendorCancellations(Request $request)
    {
        $user = $request->user();
        $vendor = Vendor::where('user_id', $user->id)->firstOrFail();

        $cancellations = OrderCancellation::with(['order.user', 'order.items.product'])
            ->whereHas('order', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $cancellations
        ], 200);
    }

    /**
     * Admin: View all cancellations
     */
    public function adminCancellations(Request $request)
    {
        $cancellations = OrderCancellation::with(['order.user', 'order.vendor.user', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $cancellations
        ], 200);
    }
}