<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VendorMiddleware;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\AddressController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/vendor/register', [AuthController::class, 'registerVendor']);
Route::post('/forgot-password', [PasswordController::class, 'forgot']);
Route::post('/reset-password', [PasswordController::class, 'reset']);

// Public product listing (multi-image supported)
Route::get('/products', [ProductController::class, 'index']);

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
    |--------------------------------------------------------------------------
    | User Profile (Customer + Vendor + Admin)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::post('/vendor/{vendor_id}/approve', [AuthController::class, 'approveVendor']);

        // Category CRUD
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Vendor Routes (multi-image product support)
    |--------------------------------------------------------------------------
    */
    Route::middleware(VendorMiddleware::class)->group(function () {
        // Vendor Profile
        Route::get('/vendor/profile', [AuthController::class, 'vendorProfile']);
        Route::put('/vendor/profile', [AuthController::class, 'updateVendorProfile']);

        // Product CRUD (multi-images supported)
        Route::post('/products', [ProductController::class, 'store']);       // Create product
        Route::put('/products/{id}', [ProductController::class, 'update']); // Update product (add/remove images)
        Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Delete product and all images

        // Vendor order status update
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/item/{id}', [CartController::class, 'update']);
    Route::delete('/cart/item/{id}', [CartController::class, 'remove']);

    // Orders
    Route::post('/checkout', [OrderController::class, 'placeOrder']);
    Route::get('/orders', [OrderController::class, 'myOrders']);

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault']);
});
