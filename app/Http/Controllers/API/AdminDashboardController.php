<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use DB;

class AdminDashboardController extends Controller
{
    /**
     * Get admin dashboard overview
     */
    public function overview(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : Carbon::now()->subDays(30);
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : Carbon::now();
            
        return response()->json([
            'users' => [
                'total' => User::where('role', 'customer')->count(),
                'new_this_month' => User::where('role', 'customer')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count(),
            ],
            'vendors' => [
                'total' => Vendor::count(),
                'active' => Vendor::where('status', 'approved')->count(),
                'pending' => Vendor::where('status', 'pending')->count(),
                'rejected' => Vendor::where('status', 'rejected')->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('stock', '>', 0)->count(),
                'out_of_stock' => Product::where('stock', 0)->count(),
            ],
            'orders' => [
                'total' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::whereIn('status', ['paid', 'processing', 'shipped'])->count(),
                'completed' => Order::where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'cancelled' => Order::where('status', 'cancelled')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ],
            'revenue' => [
                'total' => Order::where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_amount'),
                'today' => Order::where('status', 'delivered')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('total_amount'),
            ],
        ]);
    }
    
    /**
     * Get all vendors with filters
     */
    public function vendors(Request $request)
    {
        $query = Vendor::with('user');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $vendors = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($vendors);
    }
    
    /**
     * Get all users with filters
     */
    public function users(Request $request)
    {
        $query = User::query();
        
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($users);
    }
    
    /**
     * Get all orders with filters
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'vendor', 'items']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($orders);
    }
}