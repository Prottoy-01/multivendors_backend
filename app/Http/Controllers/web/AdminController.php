<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Storage; // ✅ Add this at the top with other imports
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\OrderItem; 


class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $overview = [
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'monthly_revenue' => Order::where('payment_status', 'paid')
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->sum('total_amount'),
            'today_revenue' => Order::where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'pending_vendors' => Vendor::where('status', 'pending')->count(), // ✅ FIXED THIS LINE
        ];

        return view('admin.dashboard', compact('overview'));
    }

    /**
     * Users list
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get()->toArray();
        return view('admin.users', compact('users'));
    }


    /**
 * Activate user
 */
public function activateUser($id)
{
    $user = User::findOrFail($id);
    
    // Prevent admin from changing their own status
    if ($user->id === auth()->id()) {
        return redirect()->back()->with('error', 'You cannot change your own status!');
    }
    
    $user->status = 'active';
    $user->save();

    return redirect()->back()->with('success', 'User activated successfully!');
}

/**
 * Suspend user
 */
public function suspendUser($id)
{
    $user = User::findOrFail($id);
    
    // Prevent admin from changing their own status
    if ($user->id === auth()->id()) {
        return redirect()->back()->with('error', 'You cannot change your own status!');
    }
    
    // Prevent suspending other admins
    if ($user->role === 'admin') {
        return redirect()->back()->with('error', 'You cannot suspend another admin!');
    }
    
    $user->status = 'suspended';
    $user->save();

    return redirect()->back()->with('success', 'User suspended successfully!');
}

/**
 * Ban user
 */
public function banUser($id)
{
    $user = User::findOrFail($id);
    
    // Prevent admin from changing their own status
    if ($user->id === auth()->id()) {
        return redirect()->back()->with('error', 'You cannot change your own status!');
    }
    
    // Prevent banning other admins
    if ($user->role === 'admin') {
        return redirect()->back()->with('error', 'You cannot ban another admin!');
    }
    
    $user->status = 'banned';
    $user->save();

    return redirect()->back()->with('success', 'User banned successfully!');
}

    /**
     * Vendors list
     */
    public function vendors()
    {
        $vendors = Vendor::with('user')->orderBy('created_at', 'desc')->get()->toArray();
        return view('admin.vendors', compact('vendors'));
    }

    /**
     * Approve vendor
     */
    public function approveVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->status = 'approved'; // ✅ Using 'status' column
        $vendor->save();

        return redirect()->back()->with('success', 'Vendor approved successfully!');
    }

    /**
 * Reject vendor
 */
public function rejectVendor($id)
{
    $vendor = Vendor::findOrFail($id);
    $vendor->status = 'rejected';
    $vendor->save();

    return redirect()->back()->with('success', 'Vendor rejected successfully!');
}
/**
 * Show vendor details
 */
public function vendorDetails($id)
{
    $vendor = Vendor::with(['user', 'products.category', 'products.images'])
        ->findOrFail($id);
    
    $vendor = $vendor->toArray();
    
    // Get vendor statistics
    $productIds = Product::where('vendor_id', $id)->pluck('id');
    
    $stats = [
        'total_products' => Product::where('vendor_id', $id)->count(),
        'total_orders' => Order::whereHas('items', function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->count(),
        'total_revenue' => OrderItem::whereIn('product_id', $productIds)
            ->get()
            ->sum(function($item) {
                return $item->quantity * $item->final_price;
            }),
        'pending_orders' => Order::whereHas('items', function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->where('status', 'pending')->count(),
        'completed_orders' => Order::whereHas('items', function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->where('status', 'delivered')->count(),
    ];
    
    // Get recent products
    $products = Product::where('vendor_id', $id)
        ->with(['category', 'images'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->toArray();

    return view('admin.vendor-details', compact('vendor', 'stats', 'products'));
}
    /**
     * Categories management
     */
    public function categories()
    {
        $categories = Category::orderBy('name')->get()->toArray();
        return view('admin.categories', compact('categories'));
    }

    /**
     * Store category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->all());

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with products!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Orders list
     */
    public function orders()
    {
        $orders = Order::with(['user', 'items.product', 'items.vendor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return view('admin.orders', compact('orders'));
    }

    /**
     * Coupons list
     */
    public function coupons()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get()->toArray();
        return view('admin.coupons', compact('coupons'));
    }

    /**
     * Store coupon
     */
    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1', // ✅✅✅ ADD THIS LINE ✅✅✅
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

       // Coupon::create($request->all());
       $data = $request->all();
    $data['code'] = strtoupper($request->code);
    // ✅✅✅ END FIX ✅✅✅
    
// ✅ ADD THIS: Ensure per_user_limit has a value
if (!isset($data['per_user_limit']) || $data['per_user_limit'] < 1) {
    $data['per_user_limit'] = 5; // Default: 5 uses per customer
}



    Coupon::create($data);

        return redirect()->back()->with('success', 'Coupon created successfully!');
    }

    /**
 * Delete product (Admin)
 */
public function deleteProduct($id)
{
    $product = Product::with('images')->findOrFail($id);
    
    // Delete all product images from storage
    foreach ($product->images as $image) {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }
    
    // Delete the product
    $product->delete();
    
    return redirect()->route('products.index')
        ->with('success', 'Product deleted successfully by admin!');
}
}