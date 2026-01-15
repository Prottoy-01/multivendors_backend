<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\VendorDashboardController;
use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\OrderCancellationController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VendorMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/* 🔵 GOOGLE LOGIN */
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

Route::post('/vendor/register', [AuthController::class, 'registerVendor']);
Route::post('/forgot-password', [PasswordController::class, 'forgot']);
Route::post('/reset-password', [PasswordController::class, 'reset']);

/* Public product listing (multi-image supported)
|---------------------------------------------------------------------------
| Query Parameters:
| - search: string (search in name & description)
| - category_id: integer (filter by category)
| - min_price: numeric (filter by minimum price)
| - max_price: numeric (filter by maximum price)
| - sort_by: string (options: newest, price_asc, price_desc)
| - page: integer (for pagination)
*/
Route::get('/products', [ProductController::class, 'index']);

/* Public categories - ADD THIS LINE */
Route::get('/categories', [CategoryController::class, 'index']);


/* Public product reviews */
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Routes (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [PasswordController::class, 'change']);

    /*
    |----------------------------------------------------------------------
    | User Profile (Customer + Vendor + Admin)
    |----------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    /*
    |----------------------------------------------------------------------
    | Reviews (Authenticated Users)
    |----------------------------------------------------------------------
    */
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Wishlist (Customer)
    
    */
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

    /*
    |----------------------------------------------------------------------
    | Coupons (Customer)
    |----------------------------------------------------------------------
    */
    Route::post('/coupons/validate', [CouponController::class, 'validate']);

    /*
    |----------------------------------------------------------------------
    | Cart (Customer)
    |----------------------------------------------------------------------
    */
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/item/{id}', [CartController::class, 'update']);
    Route::delete('/cart/item/{id}', [CartController::class, 'remove']);

    /*
    |----------------------------------------------------------------------
    | Orders (Customer)
    |----------------------------------------------------------------------
    */
    Route::post('/checkout', [OrderController::class, 'placeOrder']);
    Route::get('/orders', [OrderController::class, 'myOrders']);
    
    // Order Cancellation
    Route::post('/orders/{orderId}/cancel', [OrderCancellationController::class, 'cancelOrder']);
    Route::get('/orders/{orderId}/cancellation', [OrderCancellationController::class, 'getCancellationDetails']);
    Route::get('/orders/{orderId}/cancellation/check', [OrderCancellationController::class, 'checkCancellationEligibility']);

    /*
    |----------------------------------------------------------------------
    | Addresses (Customer)
    |----------------------------------------------------------------------
    */
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault']);

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */
    Route::middleware(AdminMiddleware::class)->group(function () {
        // Vendor Management
        Route::post('/vendor/{vendor_id}/approve', [AuthController::class, 'approveVendor']);

        // Category CRUD
        //Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Admin Dashboard
        Route::get('/admin/dashboard/overview', [AdminDashboardController::class, 'overview']);
        Route::get('/admin/vendors', [AdminDashboardController::class, 'vendors']);
        Route::get('/admin/users', [AdminDashboardController::class, 'users']);
        Route::get('/admin/orders', [AdminDashboardController::class, 'orders']);

        // Coupon Management
        Route::apiResource('admin/coupons', CouponController::class);

        // Review Moderation
        Route::put('/admin/reviews/{id}/moderate', [ReviewController::class, 'moderate']);
        
        // Admin Cancellations
        Route::get('/admin/cancellations', [OrderCancellationController::class, 'adminCancellations']);
    });

    /*
    |----------------------------------------------------------------------
    | Vendor Routes (multi-image product support)
    |----------------------------------------------------------------------
    */
    Route::middleware(VendorMiddleware::class)->group(function () {
        // Vendor Profile
        Route::get('/vendor/profile', [AuthController::class, 'vendorProfile']);
        Route::put('/vendor/profile', [AuthController::class, 'updateVendorProfile']);

        // Product CRUD
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Vendor order status update
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

        // Vendor Dashboard
        Route::get('/vendor/dashboard/analytics', [VendorDashboardController::class, 'analytics']);
        Route::get('/vendor/dashboard/orders', [VendorDashboardController::class, 'recentOrders']);
        Route::get('/vendor/dashboard/products', [VendorDashboardController::class, 'products']);
        
        // Vendor Cancellations
        Route::get('/vendor/cancellations', [OrderCancellationController::class, 'vendorCancellations']);
    });
});