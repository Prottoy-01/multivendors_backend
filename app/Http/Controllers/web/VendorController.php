<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Vendor dashboard
     */
    public function dashboard()
    {
        $analyticsResponse = $this->api->getVendorAnalytics();
        $analytics = $analyticsResponse['data'] ?? [];

        $ordersResponse = $this->api->getVendorOrders();
        $recentOrders = $ordersResponse['data'] ?? [];

        return view('vendor.dashboard', compact('analytics', 'recentOrders'));
    }

    /**
     * Products listing
     */
    public function products()
    {
        $response = $this->api->getVendorProducts();
        $products = $response['data'] ?? [];

        return view('vendor.products.index', compact('products'));
    }

    /**
     * Create product form
     */
    public function createProduct()
    {
        $categoriesResponse = $this->api->getCategories();
        $categories = $categoriesResponse['data'] ?? [];

        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store new product
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price', 'stock', 'category_id', 'has_offer', 'discount_type', 'discount_value', 'offer_start', 'offer_end']);

        $files = [];
        if ($request->hasFile('images')) {
            $files['images'] = $request->file('images');
        }

        $response = $this->api->createProduct($data, $files);

        if (isset($response['data'])) {
            return redirect()->route('vendor.products.index')->with('success', 'Product created successfully!');
        }

        return back()->withErrors(['error' => $response['message'] ?? 'Failed to create product'])->withInput();
    }

    /**
     * Edit product form
     */
    public function editProduct($id)
    {
        $productResponse = $this->api->getProduct($id);
        $product = $productResponse['data'] ?? null;

        if (!$product) {
            abort(404, 'Product not found');
        }

        $categoriesResponse = $this->api->getCategories();
        $categories = $categoriesResponse['data'] ?? [];

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price', 'stock', 'category_id', 'has_offer', 'discount_type', 'discount_value', 'offer_start', 'offer_end']);

        $files = [];
        if ($request->hasFile('images')) {
            $files['images'] = $request->file('images');
        }

        $response = $this->api->updateProduct($id, $data, $files);

        if (isset($response['data'])) {
            return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully!');
        }

        return back()->withErrors(['error' => $response['message'] ?? 'Failed to update product'])->withInput();
    }

    /**
     * Delete product
     */
    public function deleteProduct($id)
    {
        $response = $this->api->deleteProduct($id);

        if (isset($response['message'])) {
            return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully!');
        }

        return back()->with('error', 'Failed to delete product');
    }

    /**
     * Orders listing
     */
    public function orders()
    {
        $response = $this->api->getVendorOrders();
        $orders = $response['data'] ?? [];

        return view('vendor.orders', compact('orders'));
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $response = $this->api->updateOrderStatus($id, $request->status);

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Order status updated!');
        }

        return back()->with('error', 'Failed to update order status');
    }

    /**
     * Analytics page
     */
    public function analytics()
    {
        $response = $this->api->getVendorAnalytics();
        $analytics = $response['data'] ?? [];

        return view('vendor.analytics', compact('analytics'));
    }

    /**
     * Vendor profile
     */
    public function profile()
    {
        $response = $this->api->getProfile();
        $vendor = $response['data'] ?? [];

        return view('vendor.profile', compact('vendor'));
    }

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        $data = $request->only(['name', 'phone', 'shop_name', 'shop_description', 'address']);

        $response = $this->api->updateProfile($data);

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Profile updated successfully!');
        }

        return back()->with('error', 'Failed to update profile');
    }
}