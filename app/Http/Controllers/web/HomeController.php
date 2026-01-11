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
        // Get featured products (newest 8 products)
        $products = Product::with(['vendor', 'category', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->toArray();

        // Get popular products (products with highest ratings, 8 products)
        $popularProducts = Product::with(['vendor', 'category', 'images'])
            ->where('avg_rating', '>', 0)
            ->orderByDesc('avg_rating')
            ->orderByDesc('review_count')
            ->limit(8)
            ->get()
            ->toArray();
        
        // If not enough popular products with ratings, fill with random products
        if (count($popularProducts) < 8) {
            $additionalCount = 8 - count($popularProducts);
            $popularProductIds = array_column($popularProducts, 'id');
            $featuredProductIds = array_column($products, 'id');
            $excludeIds = array_merge($popularProductIds, $featuredProductIds);
            
            $additionalProducts = Product::with(['vendor', 'category', 'images'])
                ->whereNotIn('id', $excludeIds)
                ->inRandomOrder()
                ->limit($additionalCount)
                ->get()
                ->toArray();
            
            $popularProducts = array_merge($popularProducts, $additionalProducts);
        }

        // Get all categories
        $categories = Category::orderBy('name')->get()->toArray();

        // Get products for each category (8 products per category)
        $categoryProducts = Category::with(['products' => function($query) {
                $query->with(['vendor', 'images'])
                    ->limit(8);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'products' => $category->products->toArray()
                ];
            })
            ->toArray();

        return view('home', compact('products', 'popularProducts', 'categories', 'categoryProducts'));
    }

    /**
     * Show products listing page
     */
    public function products(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'images']);

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
        $product = Product::with([
            'vendor', 
            'category', 
            'images', 
            'reviews.user',
            'activeVariants' => function($query) {
                $query->orderBy('price', 'asc');
            }
        ])->findOrFail($id);
        
        // Calculate final price
        $product->final_price = $product->price;
        if ($product->has_offer && $product->discount_value) {
            if ($product->discount_type === 'percentage') {
                $product->final_price = $product->price - ($product->price * $product->discount_value / 100);
            } else {
                $product->final_price = $product->price - $product->discount_value;
            }
        }
        
        $product->image_urls = $product->images->map(fn($img) => asset('storage/' . $img->image_path));
        
        // Get variant attributes for selection
        $variantAttributes = [];
        $hasVariants = $product->hasVariants();
        
        if ($hasVariants) {
            foreach ($product->activeVariants as $variant) {
                foreach ($variant->attributes as $key => $value) {
                    if (!isset($variantAttributes[$key])) {
                        $variantAttributes[$key] = [];
                    }
                    if (!in_array($value, $variantAttributes[$key])) {
                        $variantAttributes[$key][] = $value;
                    }
                }
            }
        }
        
        $product = $product->toArray();
        
        // Get reviews
        $reviews = $product['reviews'] ?? [];
        
        // Get related products
        $relatedProducts = Product::where('category_id', $product['category_id'])
            ->where('id', '!=', $id)
            ->with(['images'])
            ->limit(4)
            ->get()
            ->map(function($p) {
                $p->image_urls = $p->images->map(fn($img) => asset('storage/' . $img->image_path));
                $p->final_price = $p->price;
                if ($p->has_offer && $p->discount_value) {
                    if ($p->discount_type === 'percentage') {
                        $p->final_price = $p->price - ($p->price * $p->discount_value / 100);
                    } else {
                        $p->final_price = $p->price - $p->discount_value;
                    }
                }
                return $p;
            })
            ->toArray();
        
        return view('products.show', compact('product', 'relatedProducts', 'variantAttributes', 'hasVariants', 'reviews'));
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