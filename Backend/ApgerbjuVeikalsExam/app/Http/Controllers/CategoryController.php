<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent')->orderBy('name');

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        return response()->json($query->get());
    }

    public function tree(Request $request)
    {
        $query = Category::with('children')->whereNull('parent_id')->orderBy('name');

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department' => ['required', Rule::in(['men', 'women', 'unisex'])],
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slugBase = Str::slug($data['name']);
        $slug = $slugBase;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'department' => $data['department'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category->load('parent'),
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department' => ['required', Rule::in(['men', 'women', 'unisex'])],
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slugBase = Str::slug($data['name']);
        $slug = $slugBase;
        $counter = 1;

        while (
            Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
            'department' => $data['department'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->load('parent'),
        ]);
    }

    public function destroy(Request $request, Category $category)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $hasListings = Listing::where('category', $category->name)->exists();

        if ($hasListings) {
            return response()->json([
                'message' => 'Cannot delete category that is used by listings',
            ], 422);
        }

        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category that has subcategories',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}