<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VendorMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/vendor/register', [AuthController::class, 'registerVendor']);

// Public product listing
Route::get('/products', [ProductController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Routes (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

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
    | Vendor Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(VendorMiddleware::class)->group(function () {
        Route::get('/vendor/profile', [AuthController::class, 'vendorProfile']);
        Route::put('/vendor/profile', [AuthController::class, 'updateVendorProfile']);

        // Product CRUD
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

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
});
