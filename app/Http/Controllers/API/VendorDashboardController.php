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
    /**
     * Get vendor dashboard analytics
     */
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
        
        // Total revenue (delivered orders only)
        $totalRevenue = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
            
        // Order statistics
        $totalOrders = Order::where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $pendingOrders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'paid', 'processing'])
            ->count();
            
        $completedOrders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
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
            
        // Revenue trend (daily for last 7 days)
        $revenueTrend = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        // Category-wise sales
        $categorySales = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.vendor_id', $vendor->id)
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('categories.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.final_price * order_items.quantity) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->get();
            
        return response()->json([
            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_products' => Product::where('vendor_id', $vendor->id)->count(),
                'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            ],
            'top_products' => $topProducts,
            'low_stock_products' => $lowStockProducts,
            'revenue_trend' => $revenueTrend,
            'category_sales' => $categorySales,
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