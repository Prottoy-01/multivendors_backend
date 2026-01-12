<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Storage;
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
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
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
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own status!');
        }
        
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
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own status!');
        }
        
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
        $vendor->status = 'approved';
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
     * ========================================
     * COUPONS - UPDATED FOR CATEGORY SUPPORT
     * ========================================
     */
    
    /**
     * Coupons list - Load with categories
     */
    public function coupons()
    {
        // ✅ NEW: Load coupons with categories relationship
        $coupons = Coupon::with('categories')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        // ✅ NEW: Get all categories for the form
        $categories = Category::orderBy('name')
            ->get()
            ->toArray();
        
        return view('admin.coupons', compact('coupons', 'categories'));
    }

    /**
     * Store coupon - With category support
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
            'per_user_limit' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'applies_to_all' => 'required|boolean', // ✅ NEW
            'category_ids' => 'required_if:applies_to_all,false|array', // ✅ NEW
            'category_ids.*' => 'exists:categories,id', // ✅ NEW
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($request->code);
        
        // Ensure per_user_limit has a value
        if (!isset($data['per_user_limit']) || $data['per_user_limit'] < 1) {
            $data['per_user_limit'] = 5;
        }
        
        // Set created_by to current admin
        $data['created_by'] = auth()->id();

        // ✅ NEW: Create coupon
        $coupon = Coupon::create($data);
        
        // ✅ NEW: Attach categories if not applies_to_all
        if (!$request->applies_to_all && !empty($request->category_ids)) {
            $coupon->categories()->attach($request->category_ids);
        }

        return redirect()->back()->with('success', 'Coupon created successfully!');
    }

    /**
     * ✅ NEW: Update coupon
     */
    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $request->validate([
            'is_active' => 'sometimes|boolean',
            'usage_limit' => 'sometimes|integer|min:0',
            'valid_until' => 'sometimes|date',
            'applies_to_all' => 'sometimes|boolean',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
        ]);
        
        // Update basic fields
        $coupon->update($request->only([
            'is_active', 'usage_limit', 'valid_until', 'applies_to_all'
        ]));
        
        // Update categories if provided
        if ($request->has('category_ids')) {
            if ($request->applies_to_all) {
                // If applies to all, remove all category associations
                $coupon->categories()->detach();
            } else {
                // Sync categories
                $coupon->categories()->sync($request->category_ids);
            }
        }
        
        return redirect()->back()->with('success', 'Coupon updated successfully!');
    }

    /**
     * ✅ NEW: Toggle coupon active status
     */
    public function toggleCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();
        
        $status = $coupon->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Coupon {$status} successfully!");
    }

    /**
     * ✅ NEW: Delete coupon
     */
    public function deleteCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Only delete if not used
        if ($coupon->usage_count > 0) {
            return redirect()->back()->with('error', 'Cannot delete coupon that has been used. Deactivate it instead.');
        }
        
        $coupon->delete();
        
        return redirect()->back()->with('success', 'Coupon deleted successfully!');
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