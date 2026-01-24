<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use DB;

class VendorDashboardController extends Controller
{
    
    public function analytics(Request $request)
{
    $vendor = Vendor::where('user_id', $request->user()->id)->firstOrFail();
    
    // Date range filter (default: last 30 days)
    $startDate = $request->start_date 
        ? Carbon::parse($request->start_date) 
        : Carbon::now()->subDays(30);
    $endDate = $request->end_date 
        ? Carbon::parse($request->end_date) 
        : Carbon::now();
    
    // ⭐⭐⭐ TOTAL LIFETIME REVENUE
    // This is the vendor's total_earnings field which is automatically updated:
    // - Incremented when card payment successful OR when COD order is shipped
    // - Decremented when order is cancelled (by refund amount)
    $totalRevenue = $vendor->total_earnings;
    
    // ⭐⭐⭐ PERIOD-SPECIFIC REVENUE (for selected date range)
    // Revenue from orders created in this period that reached shipped/delivered status
    $periodShippedRevenue = Order::where('vendor_id', $vendor->id)
        ->whereIn('status', ['shipped', 'delivered'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('grand_total');  // ✅ FIXED: Use grand_total (final customer payment)
    
    // Subtract refunds from cancellations in this period
    $periodCancellationLoss = DB::table('order_cancellations')
        ->join('orders', 'order_cancellations.order_id', '=', 'orders.id')
        ->where('orders.vendor_id', $vendor->id)
        ->whereBetween('order_cancellations.created_at', [$startDate, $endDate])
        ->sum('order_cancellations.refund_amount');
    
    // Net revenue for the period (what vendor actually keeps)
    $periodRevenue = $periodShippedRevenue - $periodCancellationLoss;
        
    // Order statistics
    $totalOrders = Order::where('vendor_id', $vendor->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
    $pendingOrders = Order::where('vendor_id', $vendor->id)
        ->whereIn('status', ['pending', 'paid', 'processing'])
        ->count();
    
    $shippedOrders = Order::where('vendor_id', $vendor->id)
        ->where('status', 'shipped')
        ->count();
        
    $completedOrders = Order::where('vendor_id', $vendor->id)
        ->where('status', 'delivered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
    
    $cancelledOrders = Order::where('vendor_id', $vendor->id)
        ->where('status', 'cancelled')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
    // Top selling products
    $topProducts = Product::where('vendor_id', $vendor->id)
        ->orderBy('order_count', 'desc')
        ->limit(5)
        ->get(['id', 'name', 'price', 'stock', 'order_count', 'average_rating']);
        
    // Low stock products (less than 10)
    $lowStockProducts = Product::where('vendor_id', $vendor->id)
        ->where('stock', '<', 10)
        ->orderBy('stock', 'asc')
        ->limit(10)
        ->get(['id', 'name', 'stock']);
        
    // ⭐ FIXED: Revenue trend (daily for last 7 days)
    $revenueTrend = Order::where('vendor_id', $vendor->id)
        ->whereIn('status', ['shipped', 'delivered'])
        ->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()])
        ->selectRaw('DATE(created_at) as date, SUM(grand_total) as revenue')
        //          ^^^^^^^^^^                    ^^^^^^^^^^^
        //          FIXED                         FIXED
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
    return response()->json([
        'total_revenue' => $totalRevenue,           // Lifetime earnings
        'period_revenue' => $periodRevenue,         // Revenue in date range
        'period_shipped_revenue' => $periodShippedRevenue,  // Gross revenue in period
        'period_cancellation_loss' => $periodCancellationLoss,  // Refunds in period
        'total_orders' => $totalOrders,
        'pending_orders' => $pendingOrders,
        'shipped_orders' => $shippedOrders,
        'completed_orders' => $completedOrders,
        'cancelled_orders' => $cancelledOrders,
        'top_products' => $topProducts,
        'low_stock_products' => $lowStockProducts,
        'revenue_trend' => $revenueTrend,
        'date_range' => [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ]
    ]);
}
    
    /**
     * Get recent orders
     */
    public function recentOrders(Request $request)
    {
        $vendor = Vendor::where('user_id', $request->user()->id)->firstOrFail();
        
        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($orders);
    }
    
    /**
     * Get vendor products with filters
     */
    public function products(Request $request)
    {
        $vendor = Vendor::where('user_id', $request->user()->id)->firstOrFail();
        
        $query = Product::where('vendor_id', $vendor->id)
            ->with(['category', 'images']);
            
        // Filter by stock status
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
                case 'low_stock':
                    $query->where('stock', '>', 0)->where('stock', '<', 10);
                    break;
                case 'in_stock':
                    $query->where('stock', '>=', 10);
                    break;
            }
        }
        
        // Sort
        $sortBy = $request->sort_by ?? 'newest';
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'popular':
                $query->orderBy('order_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(20);
        
        return response()->json($products);
    }
}