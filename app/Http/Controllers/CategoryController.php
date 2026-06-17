<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * List all categories
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }

    /**
     * Create new category
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Category::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:255',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('message.category_created'),
            'data' => $category,
        ], 201);
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category)
    {
        Gate::authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('message.category_updated'),
            'data' => $category,
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        // Optional: منع حذف تصنيف مرتبط بمهام
        if ($category->tasks()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('message.category_in_use'),
            ], 409);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('message.category_deleted'),
        ]);
    }
}
