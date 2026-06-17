<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    /**
     * List all tags
     */
    public function index()
    {
        $tags = Tag::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $tags,
        ]);
    }

    /**
     * Create new tag
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Tag::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'color' => 'nullable|string|max:50',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => __('message.tag_created'),
            'data' => $tag,
        ], 201);
    }

    /**
     * Delete tag
     */
    public function destroy(Tag $tag)
    {
        Gate::authorize('delete', $tag);

        // Optional: منع حذف Tag مستخدم في مهام
        if ($tag->tasks()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => __('message.tag_in_use'),
            ], 409);
        }

        $tag->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('message.tag_deleted'),
        ]);
    }
}
