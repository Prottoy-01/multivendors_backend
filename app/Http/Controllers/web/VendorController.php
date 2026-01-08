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
use App\Models\ProductVariant;

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
                return $item->quantity * $item->final_price;
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

$categories = Category::orderBy('name')->get()->toArray();

return view('vendor.products.index', compact('products', 'categories'));

    }

    /**
     * Show create product form
     */
    public function createProduct()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // ✅ STEP 5: Check if vendor is approved
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can add products. Current status: ' . ucfirst($vendor->status));
        }

        $categories = Category::orderBy('name')->get()->toArray();
        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store new product
     */
    // public function storeProduct(Request $request)
    // {
    //     $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
    //     // ✅ STEP 5: Check if vendor is approved
    //     if ($vendor->status !== 'approved') {
    //         return redirect()->route('vendor.dashboard')
    //             ->with('error', 'Your vendor account must be approved before you can add products. Current status: ' . ucfirst($vendor->status));
    //     }

    //     $request->validate([
    //         'name' => 'required|string|max:150',
    //         'description' => 'required|string',
    //         'price' => 'required|numeric|min:0',
    //         'stock' => 'required|integer|min:0',
    //         'category_id' => 'required|exists:categories,id',
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //         'has_offer' => 'sometimes|boolean',
    //         'discount_type' => 'nullable|in:percentage,fixed',
    //         'discount_value' => 'nullable|numeric|min:0',
    //         'offer_start' => 'nullable|date',
    //         'offer_end' => 'nullable|date|after:offer_start',
    //     ]);

    //     $product = Product::create([
    //         'vendor_id' => $vendor->id,
    //         'category_id' => $request->category_id,
    //         'name' => $request->name,
    //         'description' => $request->description,
    //         'price' => $request->price,
    //         'stock' => $request->stock,
    //         'has_offer' => $request->has_offer ?? false,
    //         'discount_type' => $request->discount_type,
    //         'discount_value' => $request->discount_value,
    //         'offer_start' => $request->offer_start,
    //         'offer_end' => $request->offer_end,
    //     ]);

    //     // Save multiple images
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $key => $file) {
    //             $path = $file->store('products', 'public');
    //             $product->images()->create([
    //                 'image_path' => $path,
    //                 'is_main' => $key === 0
    //             ]);
    //         }
    //     }

    //     return redirect()->route('vendor.products.index')->with('success', 'Product created successfully!');
    // }
    /**
 * Store new product
 */
public function storeProduct(Request $request)
{
    $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
    
    // ✅ Check if vendor is approved
    if ($vendor->status !== 'approved') {
        return redirect()->route('vendor.dashboard')
            ->with('error', 'Your vendor account must be approved before you can add products. Current status: ' . ucfirst($vendor->status));
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
        
        // Variant validation
        'variants' => 'nullable|array',
        'variants.*.name' => 'required_with:variants|string|max:100',
        'variants.*.price' => 'required_with:variants|numeric|min:0',
        'variants.*.stock' => 'required_with:variants|integer|min:0',
        'variants.*.sku' => 'nullable|string|max:50',
    ]);

    // Create product
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

    // ✅ Create variants if provided
    if ($request->has('variants') && is_array($request->variants)) {
        foreach ($request->variants as $variantData) {
            // Build attributes array
            $attributes = [];
            
            if (!empty($variantData['color'])) {
                $attributes['color'] = $variantData['color'];
            }
            
            if (!empty($variantData['size'])) {
                $attributes['size'] = $variantData['size'];
            }
            
            // Parse other attributes (format: key:value,key:value)
            if (!empty($variantData['other_attributes'])) {
                $otherAttrs = explode(',', $variantData['other_attributes']);
                foreach ($otherAttrs as $attr) {
                    if (strpos($attr, ':') !== false) {
                        list($key, $value) = explode(':', $attr, 2);
                        $attributes[trim($key)] = trim($value);
                    }
                }
            }
            
            // Create variant
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantData['sku'] ?? null,
                'name' => $variantData['name'],
                'attributes' => $attributes,
                'price' => $variantData['price'],
                'stock' => $variantData['stock'],
                'is_active' => true,
            ]);
        }
    }

    return redirect()->route('vendor.products.index')
        ->with('success', 'Product created successfully!' . ($request->has('variants') ? ' With ' . count($request->variants) . ' variants.' : ''));
}
    /**
     * Show edit product form
     */
    public function editProduct($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // ✅ STEP 5: Check if vendor is approved
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can edit products. Current status: ' . ucfirst($vendor->status));
        }
        
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
        
        // ✅ STEP 5: Check if vendor is approved
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can update products. Current status: ' . ucfirst($vendor->status));
        }
        
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
        
        // ✅ STEP 5: Check if vendor is approved
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can delete products. Current status: ' . ucfirst($vendor->status));
        }
        
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
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // ✅ STEP 5: Check if vendor is approved (optional for order management)
        // You can decide if rejected vendors should still be able to manage existing orders
        if ($vendor->status === 'rejected') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account has been rejected. Please contact support.');
        }

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
        
        // Calculate revenue using final_price
        $orderItems = OrderItem::whereIn('product_id', $productIds)->get();
        $totalRevenue = $orderItems->sum(function($item) {
            return $item->quantity * $item->final_price;
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


    /**
 * Store new variant
 */
public function storeVariant(Request $request, $productId)
{
    $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
    $product = Product::where('vendor_id', $vendor->id)->findOrFail($productId);
    
    $request->validate([
        'name' => 'required|string|max:100',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'sku' => 'nullable|string|max:50|unique:product_variants,sku',
    ]);
    
    // Build attributes
    $attributes = [];
    if ($request->color) $attributes['color'] = $request->color;
    if ($request->size) $attributes['size'] = $request->size;
    
    // Parse other attributes
    if ($request->other_attributes) {
        $otherAttrs = explode(',', $request->other_attributes);
        foreach ($otherAttrs as $attr) {
            if (strpos($attr, ':') !== false) {
                list($key, $value) = explode(':', $attr, 2);
                $attributes[trim($key)] = trim($value);
            }
        }
    }
    
    ProductVariant::create([
        'product_id' => $product->id,
        'sku' => $request->sku,
        'name' => $request->name,
        'attributes' => $attributes,
        'price' => $request->price,
        'stock' => $request->stock,
        'is_active' => true,
    ]);
    
    return redirect()->back()->with('success', 'Variant added successfully!');
}

/**
 * Update variant
 */
public function updateVariant(Request $request, $productId, $variantId)
{
    $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
    $product = Product::where('vendor_id', $vendor->id)->findOrFail($productId);
    $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
    
    $request->validate([
        'name' => 'required|string|max:100',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
    ]);
    
    $variant->update([
        'name' => $request->name,
        'price' => $request->price,
        'stock' => $request->stock,
    ]);
    
    return redirect()->back()->with('success', 'Variant updated successfully!');
}

/**
 * Delete variant
 */
public function deleteVariant($productId, $variantId)
{
    $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
    $product = Product::where('vendor_id', $vendor->id)->findOrFail($productId);
    $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
    
    $variant->delete();
    
    return redirect()->back()->with('success', 'Variant deleted successfully!');
}
}