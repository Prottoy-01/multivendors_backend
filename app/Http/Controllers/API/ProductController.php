<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Vendor;

class ProductController extends Controller
{
    // Public: list products
    public function index()
    {
        return response()->json(
            Product::with('vendor', 'category')->paginate(10)
        );
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
            'image' => 'nullable|image|max:2048',

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

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,

            // Offer fields
            'has_offer' => $request->has_offer ?? false,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'offer_start' => $request->offer_start,
            'offer_end' => $request->offer_end,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
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
            'image' => 'nullable|image|max:2048',

            // Offer fields
            'has_offer' => 'sometimes|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'offer_start' => 'nullable|date',
            'offer_end' => 'nullable|date|after:offer_start',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->fill($request->only([
            'name',
            'description',
            'price',
            'stock',
            'category_id',
            'has_offer',
            'discount_type',
            'discount_value',
            'offer_start',
            'offer_end',
        ]));

        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
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

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
