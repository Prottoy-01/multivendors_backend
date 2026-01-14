<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\AdminController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home & Products
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [HomeController::class, 'products'])->name('products.index');
Route::get('/products/{id}', [HomeController::class, 'productDetail'])->name('products.show');
Route::get('/category/{id}', [HomeController::class, 'category'])->name('category.show');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/vendor/register', [AuthController::class, 'showVendorRegister'])->name('vendor.register');
    Route::post('/vendor/register', [AuthController::class, 'vendorRegister']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [CustomerController::class, 'orderDetail'])->name('orders.show');
    Route::get('/wishlist', [CustomerController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/toggle', [CustomerController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::get('/cart', [CustomerController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [CustomerController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update/{id}', [CustomerController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CustomerController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/apply-coupon', [CustomerController::class, 'applyCoupon'])->name('coupon.apply');
Route::post('/remove-coupon', [CustomerController::class, 'removeCoupon'])->name('coupon.remove');
    Route::get('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CustomerController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
    Route::post('/profile', [CustomerController::class, 'updateProfile'])->name('profile.update');
    Route::get('/addresses', [CustomerController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [CustomerController::class, 'storeAddress'])->name('addresses.store');
    Route::post('/reviews', [CustomerController::class, 'storeReview'])->name('reviews.store');

    // In routes/web.php:
Route::post('/orders/{id}/cancel', [CustomerController::class, 'cancelOrder'])->name('orders.cancel');
    Route::get('/wallet', [CustomerController::class, 'wallet'])->name('wallet');

    //add payment route
    // Payment routes
// Route::post('/payment/stripe', [CustomerController::class, 'processStripePayment'])->name('payment.stripe');
// Route::get('/payment/success/{order}', [CustomerController::class, 'success'])->name('payment.success');
// Route::get('/payment/failed', [CustomerController::class, 'failed'])->name('payment.failed');
// ✅ NEW: Stripe Checkout Routes

    
});
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/stripe/checkout', [\App\Http\Controllers\Web\PaymentController::class, 'createCheckoutSession'])->name('payment.stripe.checkout');
    Route::get('/payment/stripe/success', [\App\Http\Controllers\Web\PaymentController::class, 'handleSuccess'])->name('payment.stripe.success');
    //Route::get('/payment/success/{order}', [\App\Http\Controllers\Web\PaymentController::class, 'success'])->name('payment.success');
   Route::get('/payment/success', [\App\Http\Controllers\Web\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [\App\Http\Controllers\Web\PaymentController::class, 'failed'])->name('payment.failed');
});
/*
|--------------------------------------------------------------------------
| Vendor Routes (Using your existing VendorMiddleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\VendorMiddleware::class])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [VendorController::class, 'products'])->name('products.index');
    Route::get('/products/create', [VendorController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [VendorController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [VendorController::class, 'editProduct'])->name('products.edit');
    Route::post('/products/{id}', [VendorController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [VendorController::class, 'deleteProduct'])->name('products.destroy');
    Route::get('/orders', [VendorController::class, 'orders'])->name('orders');
    Route::post('/orders/{id}/status', [VendorController::class, 'updateOrderStatus'])->name('orders.status');
    Route::get('/analytics', [VendorController::class, 'analytics'])->name('analytics');
    Route::get('/profile', [VendorController::class, 'profile'])->name('profile');
    Route::post('/profile', [VendorController::class, 'updateProfile'])->name('profile.update');
    // Variant management routes - ✅ ADD THESE
    Route::post('/products/{product}/variants', [VendorController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('/products/{product}/variants/{variant}', [VendorController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [VendorController::class, 'deleteVariant'])->name('products.variants.delete');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Using your existing AdminMiddleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('users.activate'); // ✅ ADD
    Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend'); // ✅ ADD
    Route::post('/users/{id}/ban', [AdminController::class, 'banUser'])->name('users.ban'); // ✅ ADD
    Route::get('/vendors', [AdminController::class, 'vendors'])->name('vendors');
    Route::post('/vendors/{id}/approve', [AdminController::class, 'approveVendor'])->name('vendors.approve');
        Route::post('/vendors/{id}/reject', [AdminController::class, 'rejectVendor'])->name('vendors.reject'); // ✅ ADD THIS
        Route::get('/vendors/{id}/details', [AdminController::class, 'vendorDetails'])->name('vendors.details'); // ✅ ADD THIS
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete'); // ✅ ADD THIS
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
   // Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
   // Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');



    // Coupons management - UPDATED
Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
Route::put('/coupons/{id}', [AdminController::class, 'updateCoupon'])->name('coupons.update'); // ✅ NEW
Route::post('/coupons/{id}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle'); // ✅ NEW
Route::delete('/coupons/{id}', [AdminController::class, 'deleteCoupon'])->name('coupons.delete'); // ✅ NEW
});