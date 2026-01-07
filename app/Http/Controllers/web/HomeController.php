<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show homepage
     */
    public function index()
    {
        // Get featured products directly from database
        // $products = Product::with(['vendor', 'category', 'images'])
        //     ->orderBy('created_at', 'desc')
        //     ->limit(8)
        //     ->get()
        //     ->toArray();
        $products = Product::with(['vendor', 'category', 'images'])
    ->withAvg(['reviews' => function ($q) {
        $q->where('is_approved', true);
    }], 'rating')
    ->withCount(['reviews as review_count' => function ($q) {
        $q->where('is_approved', true);
    }])
    ->orderBy('created_at', 'desc')
    ->limit(8)
    ->get()
    ->toArray();

        
        // Get categories directly from database
        $categories = Category::orderBy('name')->get()->toArray();

        return view('home', compact('products', 'categories'));
    }

    /**
     * Show products listing page
     */
    public function products(Request $request)
    {
       // $query = Product::with(['vendor', 'category', 'images']);
       $query = Product::with(['vendor', 'category', 'images'])
    ->withAvg(['reviews' => function ($q) {
        $q->where('is_approved', true);
    }], 'rating')
    ->withCount(['reviews as review_count' => function ($q) {
        $q->where('is_approved', true);
    }]);


        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort by
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $productsData = $query->paginate(15);
        $products = $productsData->items();
        
        $pagination = [
            'current_page' => $productsData->currentPage(),
            'last_page' => $productsData->lastPage(),
            'per_page' => $productsData->perPage(),
            'total' => $productsData->total(),
        ];

        // Get categories for filter
        $categories = Category::orderBy('name')->get()->toArray();

        return view('products.index', compact('products', 'categories', 'pagination'));
    }

    /**
     * Show product detail page
     */
    public function productDetail($id)
    {
        $product = Product::with(['vendor', 'category', 'images', 'reviews.user'])
            ->findOrFail($id);
        
        $product = $product->toArray();
        
        // Get reviews
        $reviews = $product['reviews'] ?? [];

        // Get related products (same category)
        $relatedProducts = Product::with(['vendor', 'category', 'images'])
            ->where('category_id', $product['category_id'])
            ->where('id', '!=', $id)
            ->limit(4)
            ->get()
            ->toArray();

        return view('products.show', compact('product', 'reviews', 'relatedProducts'));
    }

    /**
     * Show products by category
     */
    public function category($id)
    {
        $query = Product::with(['vendor', 'category', 'images'])
            ->where('category_id', $id);

        $productsData = $query->paginate(15);
        $products = $productsData->items();
        
        $pagination = [
            'current_page' => $productsData->currentPage(),
            'last_page' => $productsData->lastPage(),
            'per_page' => $productsData->perPage(),
            'total' => $productsData->total(),
        ];

        // Get categories
        $categories = Category::orderBy('name')->get()->toArray();
        
        $currentCategory = Category::find($id);
        if ($currentCategory) {
            $currentCategory = $currentCategory->toArray();
        }

        return view('products.index', compact('products', 'categories', 'pagination', 'currentCategory'));
    }
}