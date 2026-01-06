<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Show homepage
     */
    public function index()
    {
        // Get featured products
        $response = $this->api->getProducts(['page' => 1, 'per_page' => 8]);
        $products = $response['data'] ?? [];
        
        // Get categories
        $categoriesResponse = $this->api->getCategories();
        $categories = $categoriesResponse['data'] ?? [];

        return view('home', compact('products', 'categories'));
    }

    /**
     * Show products listing page
     */
    public function products(Request $request)
    {
        $params = [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'sort_by' => $request->get('sort_by', 'newest'),
            'page' => $request->get('page', 1),
        ];

        $response = $this->api->getProducts($params);
        $products = $response['data'] ?? [];
        $pagination = [
            'current_page' => $response['current_page'] ?? 1,
            'last_page' => $response['last_page'] ?? 1,
            'per_page' => $response['per_page'] ?? 15,
            'total' => $response['total'] ?? 0,
        ];

        // Get categories for filter
        $categoriesResponse = $this->api->getCategories();
        $categories = $categoriesResponse['data'] ?? [];

        return view('products.index', compact('products', 'categories', 'pagination'));
    }

    /**
     * Show product detail page
     */
    public function productDetail($id)
    {
        $productResponse = $this->api->getProduct($id);
        
        if (!isset($productResponse['data'])) {
            abort(404, 'Product not found');
        }

        $product = $productResponse['data'];
        
        // Get product reviews
        $reviewsResponse = $this->api->getProductReviews($id);
        $reviews = $reviewsResponse['data'] ?? [];

        // Get related products (same category)
        $relatedResponse = $this->api->getProducts([
            'category_id' => $product['category_id'],
            'per_page' => 4
        ]);
        $relatedProducts = $relatedResponse['data'] ?? [];

        return view('products.show', compact('product', 'reviews', 'relatedProducts'));
    }

    /**
     * Show products by category
     */
    public function category($id)
    {
        $params = [
            'category_id' => $id,
            'page' => request()->get('page', 1),
        ];

        $response = $this->api->getProducts($params);
        $products = $response['data'] ?? [];
        $pagination = [
            'current_page' => $response['current_page'] ?? 1,
            'last_page' => $response['last_page'] ?? 1,
            'per_page' => $response['per_page'] ?? 15,
            'total' => $response['total'] ?? 0,
        ];

        // Get categories
        $categoriesResponse = $this->api->getCategories();
        $categories = $categoriesResponse['data'] ?? [];
        
        $currentCategory = collect($categories)->firstWhere('id', $id);

        return view('products.index', compact('products', 'categories', 'pagination', 'currentCategory'));
    }
}