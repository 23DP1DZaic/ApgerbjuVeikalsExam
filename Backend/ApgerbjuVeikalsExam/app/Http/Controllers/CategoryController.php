<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::orderBy('name')->get()
        );
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
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
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
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
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

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}