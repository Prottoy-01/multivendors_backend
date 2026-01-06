<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiService
{
    protected $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000/api');
    }

    /**
     * Get authorization header with token
     */
    protected function getHeaders()
    {
        $token = Session::get('api_token');
        
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $token ? "Bearer $token" : '',
        ];
    }

    /**
     * Make GET request
     */
    public function get($endpoint, $params = [])
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->baseUrl . $endpoint, $params);

        return $response->json();
    }

    /**
     * Make POST request
     */
    public function post($endpoint, $data = [])
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseUrl . $endpoint, $data);

        return $response->json();
    }

    /**
     * Make POST request with file upload
     */
    public function postWithFiles($endpoint, $data = [], $files = [])
    {
        $token = Session::get('api_token');
        $request = Http::withToken($token);

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $index => $singleFile) {
                    $request = $request->attach(
                        "{$key}[{$index}]",
                        file_get_contents($singleFile->path()),
                        $singleFile->getClientOriginalName()
                    );
                }
            } else {
                $request = $request->attach(
                    $key,
                    file_get_contents($file->path()),
                    $file->getClientOriginalName()
                );
            }
        }

        $response = $request->post($this->baseUrl . $endpoint, $data);
        return $response->json();
    }

    /**
     * Make PUT request
     */
    public function put($endpoint, $data = [])
    {
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->baseUrl . $endpoint, $data);

        return $response->json();
    }

    /**
     * Make DELETE request
     */
    public function delete($endpoint)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->delete($this->baseUrl . $endpoint);

        return $response->json();
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication APIs
    |--------------------------------------------------------------------------
    */

    public function login($credentials)
    {
        return $this->post('/login', $credentials);
    }

    public function register($data)
    {
        return $this->post('/register', $data);
    }

    public function registerVendor($data)
    {
        return $this->post('/vendor/register', $data);
    }

    public function logout()
    {
        return $this->post('/logout');
    }

    /*
    |--------------------------------------------------------------------------
    | Product APIs
    |--------------------------------------------------------------------------
    */

    public function getProducts($params = [])
    {
        return $this->get('/products', $params);
    }

    public function getProduct($id)
    {
        return $this->get("/products/{$id}");
    }

    public function createProduct($data, $files = [])
    {
        return $this->postWithFiles('/products', $data, $files);
    }

    public function updateProduct($id, $data, $files = [])
    {
        if (!empty($files)) {
            return $this->postWithFiles("/products/{$id}", array_merge($data, ['_method' => 'PUT']), $files);
        }
        return $this->put("/products/{$id}", $data);
    }

    public function deleteProduct($id)
    {
        return $this->delete("/products/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | Category APIs
    |--------------------------------------------------------------------------
    */

    public function getCategories()
    {
        return $this->get('/categories');
    }

    public function createCategory($data)
    {
        return $this->post('/categories', $data);
    }

    public function updateCategory($id, $data)
    {
        return $this->put("/categories/{$id}", $data);
    }

    public function deleteCategory($id)
    {
        return $this->delete("/categories/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | Cart APIs
    |--------------------------------------------------------------------------
    */

    public function getCart()
    {
        return $this->get('/cart');
    }

    public function addToCart($data)
    {
        return $this->post('/cart/add', $data);
    }

    public function updateCartItem($id, $data)
    {
        return $this->put("/cart/item/{$id}", $data);
    }

    public function removeFromCart($id)
    {
        return $this->delete("/cart/item/{$id}");
    }

    /*
    |--------------------------------------------------------------------------
    | Order APIs
    |--------------------------------------------------------------------------
    */

    public function placeOrder($data)
    {
        return $this->post('/checkout', $data);
    }

    public function getOrders()
    {
        return $this->get('/orders');
    }

    public function updateOrderStatus($id, $status)
    {
        return $this->put("/orders/{$id}/status", ['status' => $status]);
    }

    /*
    |--------------------------------------------------------------------------
    | Wishlist APIs
    |--------------------------------------------------------------------------
    */

    public function getWishlist()
    {
        return $this->get('/wishlist');
    }

    public function toggleWishlist($productId)
    {
        return $this->post('/wishlist/toggle', ['product_id' => $productId]);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile APIs
    |--------------------------------------------------------------------------
    */

    public function getProfile()
    {
        return $this->get('/profile');
    }

    public function updateProfile($data)
    {
        return $this->post('/profile', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Address APIs
    |--------------------------------------------------------------------------
    */

    public function getAddresses()
    {
        return $this->get('/addresses');
    }

    public function createAddress($data)
    {
        return $this->post('/addresses', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Review APIs
    |--------------------------------------------------------------------------
    */

    public function getProductReviews($productId)
    {
        return $this->get("/products/{$productId}/reviews");
    }

    public function createReview($data)
    {
        return $this->post('/reviews', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Vendor Dashboard APIs
    |--------------------------------------------------------------------------
    */

    public function getVendorAnalytics()
    {
        return $this->get('/vendor/dashboard/analytics');
    }

    public function getVendorOrders()
    {
        return $this->get('/vendor/dashboard/orders');
    }

    public function getVendorProducts()
    {
        return $this->get('/vendor/dashboard/products');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard APIs
    |--------------------------------------------------------------------------
    */

    public function getAdminOverview()
    {
        return $this->get('/admin/dashboard/overview');
    }

    public function getUsers()
    {
        return $this->get('/admin/users');
    }

    public function getVendors()
    {
        return $this->get('/admin/vendors');
    }

    public function approveVendor($vendorId)
    {
        return $this->post("/vendor/{$vendorId}/approve");
    }

    public function getAdminOrders()
    {
        return $this->get('/admin/orders');
    }

    public function getCoupons()
    {
        return $this->get('/admin/coupons');
    }

    public function createCoupon($data)
    {
        return $this->post('/admin/coupons', $data);
    }

    public function validateCoupon($code)
    {
        return $this->post('/coupons/validate', ['code' => $code]);
    }
}