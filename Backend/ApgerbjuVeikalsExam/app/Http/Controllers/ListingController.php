<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
        public function index(Request $request)
        {
            $query = Listing::with('images')->latest();

            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%");
                });
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->filled('section')) {
                if ($request->section === 'trending') {
                    $query->orderBy('created_at', 'desc');
                }
            }

            return $query->get();

            if ($request->filled('category')) {
                $query->where('category', $request->category);
}
        }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'brand' => 'required|string',
            'color' => 'required|string',
            'size' => 'required|string',
            'condition' => 'required|string',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'gender' => 'required|string',
        ]);

        unset($data['images']);

        $listing = Listing::create($data);

        foreach ($request->file('images') as $image) {
            $path = $image->store('listings', 'public');

            $listing->images()->create([
                'image_path' => $path,
            ]);
        }

        return $listing->load('images');
    }


        public function show(Listing $listing)
        {
            return $listing->load('images');
        }



    public function update(Request $request, Listing $listing)
    {
    $data = $request->validate([
        'price' => 'required|numeric|min:0',
    ]);

    $listing->update($data);

    return response()->json($listing);
}



    public function destroy(Request $request, Listing $listing)
    {
        $userId = $request->input('user_id');

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        if ($user->role !== 'admin' && $listing->user_id !== $user->id) {
            return response()->json(['message' => 'No permission'], 403);
        }

        $listing->delete();

        return response()->json(['message' => 'Listing deleted']);
    }




}