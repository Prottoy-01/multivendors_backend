<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $response = $this->api->getAdminOverview();
        $overview = $response['data'] ?? [];

        return view('admin.dashboard', compact('overview'));
    }

    /**
     * Users management
     */
    public function users()
    {
        $response = $this->api->getUsers();
        $users = $response['data'] ?? [];

        return view('admin.users', compact('users'));
    }

    /**
     * Vendors management
     */
    public function vendors()
    {
        $response = $this->api->getVendors();
        $vendors = $response['data'] ?? [];

        return view('admin.vendors', compact('vendors'));
    }

    /**
     * Approve vendor
     */
    public function approveVendor($id)
    {
        $response = $this->api->approveVendor($id);

        if (isset($response['message'])) {
            return redirect()->back()->with('success', 'Vendor approved successfully!');
        }

        return back()->with('error', 'Failed to approve vendor');
    }

    /**
     * Categories management
     */
    public function categories()
    {
        $response = $this->api->getCategories();
        $categories = $response['data'] ?? [];

        return view('admin.categories', compact('categories'));
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $response = $this->api->createCategory($request->all());

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Category created successfully!');
        }

        return back()->with('error', 'Failed to create category');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $response = $this->api->updateCategory($id, $request->all());

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Category updated successfully!');
        }

        return back()->with('error', 'Failed to update category');
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $response = $this->api->deleteCategory($id);

        if (isset($response['message'])) {
            return redirect()->back()->with('success', 'Category deleted successfully!');
        }

        return back()->with('error', 'Failed to delete category');
    }

    /**
     * Orders management
     */
    public function orders()
    {
        $response = $this->api->getAdminOrders();
        $orders = $response['data'] ?? [];

        return view('admin.orders', compact('orders'));
    }

    /**
     * Coupons management
     */
    public function coupons()
    {
        $response = $this->api->getCoupons();
        $coupons = $response['data'] ?? [];

        return view('admin.coupons', compact('coupons'));
    }

    /**
     * Store new coupon
     */
    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        $response = $this->api->createCoupon($request->all());

        if (isset($response['data'])) {
            return redirect()->back()->with('success', 'Coupon created successfully!');
        }

        return back()->with('error', 'Failed to create coupon');
    }
}