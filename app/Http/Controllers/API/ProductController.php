<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;

class ProductController extends Controller
{
    // Public: list products with search, filter, and sorting
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'images']);

        // Search by name or description
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
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        // Sorting: newest, price asc, price desc
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
        $products = $query->paginate(10);

        // Add image URLs
        $products->getCollection()->transform(function ($product) {
            $product->images_url = $product->images->map(fn($img) => asset('storage/' . $img->image_path));
            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully',
            'data' => $products
        ]);
    }

    // Vendor: create product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpg,png,webp|max:2048',

            // Offer fields
            'has_offer' => 'sometimes|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'offer_start' => 'nullable|date',
            'offer_end' => 'nullable|date|after:offer_start',
        ]);

        $vendor = Vendor::where('user_id', $request->user()->id)->first();
        if (!$vendor || $vendor->status !== 'approved') {
            return response()->json(['message' => 'Vendor not approved'], 403);
        }

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'has_offer' => $request->has_offer ?? false,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'offer_start' => $request->offer_start,
            'offer_end' => $request->offer_end,
        ]);

        // Save multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_main' => $key === 0
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('images')
        ], 201);
    }

    // Vendor: update product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $vendor = Vendor::where('user_id', $request->user()->id)->first();
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'remove_images' => 'sometimes|array', // IDs of images to delete

            // Offer fields
            'has_offer' => 'sometimes|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'offer_start' => 'nullable|date',
            'offer_end' => 'nullable|date|after:offer_start',
        ]);

        // Remove selected images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imgId) {
                $img = $product->images()->find($imgId);
                if ($img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_main' => false
                ]);
            }
        }

        // Update product info
        $product->update($request->only([
            'name', 'description', 'price', 'stock', 'category_id',
            'has_offer', 'discount_type', 'discount_value', 'offer_start', 'offer_end'
        ]));

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('images')
        ]);
    }

    // Vendor: delete product
    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $vendor = Vendor::where('user_id', $request->user()->id)->first();
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete all images
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
