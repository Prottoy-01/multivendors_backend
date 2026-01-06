<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Vendor dashboard
     */
    public function dashboard()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // Get vendor's product IDs
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');
        
        // Calculate total revenue using price * quantity
        $totalRevenue = OrderItem::whereIn('product_id', $productIds)
            ->get()
            ->sum(function($item) {
                return $item->quantity * $item->price;
            });
        
        // Get analytics
        $analytics = [
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'total_orders' => OrderItem::whereIn('product_id', $productIds)->count(),
            'total_revenue' => $totalRevenue,
            'pending_orders' => Order::whereHas('items', function($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })->where('status', 'pending')->count(),
        ];
        
        // Get recent orders (orders that contain vendor's products)
        $recentOrders = Order::whereHas('items', function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->with(['user', 'items' => function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        }, 'items.product'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->toArray();

        return view('vendor.dashboard', compact('analytics', 'recentOrders'));
    }

    /**
     * Products list
     */
    public function products()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        $products = Product::where('vendor_id', $vendor->id)
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($product) {
                $product->image_urls = $product->images->map(fn($img) => asset('storage/' . $img->image_path));
                return $product;
            })
            ->toArray();

        return view('vendor.products.index', compact('products'));
    }

    /**
     * Show create product form
     */
    public function createProduct()
    {
        $categories = Category::orderBy('name')->get()->toArray();
        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store new product
     */
    public function storeProduct(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        if ($vendor->status !== 'approved') {
            return redirect()->back()->with('error', 'Your vendor account is not approved yet.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'has_offer' => 'sometimes|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'offer_start' => 'nullable|date',
            'offer_end' => 'nullable|date|after:offer_start',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'has_offer' => $request->has_offer ?? false,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'offer_start' => $request->offer_start,
            'offer_end' => $request->offer_end,
        ]);

        // Save multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_main' => $key === 0
                ]);
            }
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Show edit product form
     */
    public function editProduct($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->with(['category', 'images'])
            ->firstOrFail();
        
        $product->image_urls = $product->images->map(fn($img) => asset('storage/' . $img->image_path));
        $product = $product->toArray();
        
        $categories = Category::orderBy('name')->get()->toArray();

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'has_offer' => 'sometimes|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'offer_start' => 'nullable|date',
            'offer_end' => 'nullable|date|after:offer_start',
        ]);

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_main' => false
                ]);
            }
        }

        // Update product info
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'has_offer' => $request->has_offer ?? false,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'offer_start' => $request->offer_start,
            'offer_end' => $request->offer_end,
        ]);

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Delete product
     */
    public function destroyProduct($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();

        // Delete all images
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image_path);
            $img->delete();
        }

        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Orders list
     */
    public function orders()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // Get vendor's product IDs
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');
        
        $orders = Order::whereHas('items', function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->with(['user', 'items' => function($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        }, 'items.product.images'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->toArray();

        return view('vendor.orders', compact('orders'));
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    /**
     * Vendor profile
     */
    public function profile()
    {
        $user = Auth::user();
        $vendorData = Vendor::where('user_id', $user->id)->first();
        
        $vendor = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'vendor' => $vendorData ? $vendorData->toArray() : []
        ];

        return view('vendor.profile', compact('vendor'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $vendorData = Vendor::where('user_id', $user->id)->firstOrFail();

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        $vendorData->update([
            'shop_name' => $request->shop_name,
            'shop_description' => $request->shop_description,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Analytics
     */
    public function analytics()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // Get vendor's product IDs
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');
        
        // Calculate revenue
        $orderItems = OrderItem::whereIn('product_id', $productIds)->get();
        $totalRevenue = $orderItems->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $avgOrderValue = $orderItems->count() > 0 
            ? $totalRevenue / $orderItems->count() 
            : 0;
        
        $analytics = [
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'total_orders' => $orderItems->count(),
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
        ];

        return view('vendor.analytics', compact('analytics'));
    }
}