<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Models\Category;

class ListingController extends Controller
{
public function index(Request $request)
{
    $query = Listing::with('images')->latest();

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }

    if ($request->filled('gender')) {
        $query->where('gender', $request->gender);
    }

    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    if ($request->filled('brand')) {
        $query->where('brand', 'like', '%' . $request->brand . '%');
    }

    if ($request->filled('size')) {
        $query->where('size', $request->size);
    }

    if ($request->filled('color')) {
        $query->where('color', $request->color);
    }

    if ($request->filled('condition')) {
        $query->where('condition', $request->condition);
    }

    if ($request->filled('min_price')) {
        $query->where('price', '>=', (float) $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', (float) $request->max_price);
    }

    if ($request->boolean('has_images')) {
        $query->has('images');
    }

    if ($request->filled('sort')) {
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;

            case 'price_high':
                $query->orderBy('price', 'desc');
                break;

            case 'title_az':
                $query->orderBy('title', 'asc');
                break;

            case 'newest':
            default:
                $query->latest();
                break;
        }
    }

    return response()->json($query->get());
}

    public function show(Listing $listing)
    {
        return response()->json($listing->load('images'));
    }

public function store(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated',
        ], 401);
    }

    $data = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'category' => 'required|string',
        'gender' => 'required|string',
        'brand' => 'required|string',
        'color' => 'required|string',
        'size' => 'required|string',
        'condition' => 'required|string',
        'images' => 'required|array|min:1|max:5',
        'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $categoryExists = Category::where('name', $data['category'])->exists();

if (!$categoryExists) {
    return response()->json([
        'message' => 'Selected category does not exist',
    ], 422);
}

    unset($data['images']);

    $listing = $user->listings()->create($data);

    foreach ($request->file('images') as $image) {
        $path = $image->store('listings', 'public');

        $listing->images()->create([
            'image_path' => $path,
        ]);
    }

    return response()->json($listing->load('images'), 201);
}

    public function update(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $listing->user_id !== $user->id) {
            return response()->json(['message' => 'No permission'], 403);
        }

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $listing->update($data);

        return response()->json($listing);
    }

    public function destroy(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $listing->user_id !== $user->id) {
            return response()->json(['message' => 'No permission'], 403);
        }

        $listing->delete();

        return response()->json(['message' => 'Listing deleted']);
    }


    public function userListings(\App\Models\User $user)
    {
        return response()->json(
            $user->listings()->with('images')->latest()->get()
        );
    }

}