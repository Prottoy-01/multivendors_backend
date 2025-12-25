<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        return response()->json(Category::all());
    }

    // Create new category (Admin only)
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $category = Category::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    // Update category (Admin only)
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->save();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    // Delete category (Admin only)
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
