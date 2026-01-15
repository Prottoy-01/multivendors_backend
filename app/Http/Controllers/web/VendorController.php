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
use Illuminate\Support\Facades\Log;
use App\Models\ProductVariant;

class VendorController extends Controller
{
    /**
     * Vendor dashboard
     * ⭐ CRITICAL FIX: Now uses vendor->total_earnings which is automatically updated
     */
    public function dashboard()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // Get vendor's product IDs
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');
        
        // ⭐⭐⭐ CRITICAL FIX: Use vendor's total_earnings field
        // This field is automatically updated:
        // - Incremented when order is marked as "shipped"
        // - Decremented when order is cancelled (by refund amount)
        $totalRevenue = $vendor->total_earnings ?? 0;

        // Get analytics
        $analytics = [
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'total_orders' => Order::where('vendor_id', $vendor->id)->count(),
            'total_revenue' => $totalRevenue,
            'pending_orders' => Order::where('vendor_id', $vendor->id)
                ->whereIn('status', ['pending', 'paid', 'processing'])
                ->count(),
            'shipped_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'shipped')
                ->count(),
            'completed_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'cancelled')
                ->count(),
        ];
        
        // Get recent orders
        $recentOrders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.product'])
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

    return redirect()->route('vendor.products.index')->with('success', 'Product created successfully!');
}

    public function editProduct($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can edit products. Current status: ' . ucfirst($vendor->status));
        }
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->with('images')
            ->firstOrFail()
            ->toArray();
        
        $product['image_urls'] = collect($product['images'] ?? [])->map(function($img) {
            return asset('storage/' . $img['image_path']);
        })->toArray();

        $categories = Category::orderBy('name')->get()->toArray();

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
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

        $product->update([
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

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_main' => $key === 0 && $product->images()->count() === 0
                ]);
            }
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully!');
    }

    public function destroyProduct($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        if ($vendor->status !== 'approved') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account must be approved before you can delete products. Current status: ' . ucfirst($vendor->status));
        }
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();

        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image_path);
            $img->delete();
        }

        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully!');
    }

    public function orders()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.product.images'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return view('vendor.orders', compact('orders'));
    }

    /**
     * Update order status
     * ⭐⭐⭐ CRITICAL FIX: Now properly updates vendor earnings when shipped
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        if ($vendor->status === 'rejected') {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Your vendor account has been rejected. Please contact support.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::with('vendor')->findOrFail($id);
        
        // Verify this order belongs to the vendor
        if ($order->vendor_id !== $vendor->id) {
            return redirect()->back()->with('error', 'Unauthorized access to this order');
        }
        
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        DB::beginTransaction();
        
        try {
            // Update order status
            $order->status = $newStatus;
            $order->save();
            
            // ⭐⭐⭐ CRITICAL: Track vendor earnings when order is SHIPPED
            if ($oldStatus !== Order::STATUS_SHIPPED && $newStatus === Order::STATUS_SHIPPED) {
                $orderAmount = $order->grand_total ?? $order->total_amount;
                
                // Add to vendor's total_earnings
                $vendor->increment('total_earnings', $orderAmount);
                
                Log::info('Vendor earnings updated on order shipped', [
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'amount_added' => $orderAmount,
                    'new_total_earnings' => $vendor->fresh()->total_earnings,
                    'timestamp' => now()
                ]);
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Order status updated successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order status update failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

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
     * ⭐ CRITICAL FIX: Uses vendor->total_earnings for accurate revenue
     */
    public function analytics()
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        
        // ⭐ Use vendor's total_earnings for accurate revenue
        $totalRevenue = $vendor->total_earnings ?? 0;
        
        $totalOrders = Order::where('vendor_id', $vendor->id)->count();
        $avgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;
        
        $analytics = [
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
            'pending_orders' => Order::where('vendor_id', $vendor->id)
                ->whereIn('status', ['pending', 'paid', 'processing'])
                ->count(),
            'shipped_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'shipped')
                ->count(),
            'completed_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'cancelled')
                ->count(),
        ];

        // Get cancellation statistics
        $cancellationStats = DB::table('order_cancellations')
            ->join('orders', 'order_cancellations.order_id', '=', 'orders.id')
            ->where('orders.vendor_id', $vendor->id)
            ->selectRaw('
                COUNT(*) as total_cancellations,
                SUM(order_cancellations.original_amount) as total_original_amount,
                SUM(order_cancellations.refund_amount) as total_refund_amount,
                SUM(order_cancellations.vendor_retention) as total_vendor_retention,
                AVG(order_cancellations.refund_percentage) as avg_refund_percentage
            ')
            ->first();
        
        $analytics['cancellation_stats'] = [
            'total_cancellations' => $cancellationStats->total_cancellations ?? 0,
            'total_refund_amount' => round($cancellationStats->total_refund_amount ?? 0, 2),
            'total_vendor_retention' => round($cancellationStats->total_vendor_retention ?? 0, 2),
        ];

        return view('vendor.analytics', compact('analytics'));
    }


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

public function deleteVariant($productId, $variantId)
{
    $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
    $product = Product::where('vendor_id', $vendor->id)->findOrFail($productId);
    $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
    
    $variant->delete();
    
    return redirect()->back()->with('success', 'Variant deleted successfully!');
}
}