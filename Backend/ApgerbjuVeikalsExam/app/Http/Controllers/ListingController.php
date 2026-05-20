<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Purchase;

class ListingController extends Controller
{
    private function getUserIdFromBearerToken(Request $request): ?int
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return null;
        }

        return $accessToken->tokenable->id;
    }

    private function addInteractionState($listings, ?int $userId)
    {
        return $listings->map(function ($listing) use ($userId) {
            $listing->liked_by_me = false;
            $listing->favorited_by_me = false;

            if ($userId) {
                $listing->liked_by_me = $listing->likes()
                    ->where('user_id', $userId)
                    ->exists();

                $listing->favorited_by_me = $listing->favorites()
                    ->where('user_id', $userId)
                    ->exists();
            }

            return $listing;
        });
    }

    public function index(Request $request)
    {
        $query = Listing::with('images')
            ->where(function ($q) {
                $q->where('status', 'available')
                    ->orWhereNull('status');
            })
            ->latest();

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

        if ($request->filled('parent_category')) {
    $parentCategoryQuery = Category::where('name', $request->parent_category)
        ->whereNull('parent_id');

    if ($request->filled('gender')) {
        $parentCategoryQuery->where('department', $request->gender);
    }

    $parentCategory = $parentCategoryQuery->first();

    if ($parentCategory) {
        $childCategoryNames = Category::where('parent_id', $parentCategory->id)
            ->pluck('name')
            ->toArray();

        $query->whereIn('category', $childCategoryNames);
    }
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
                case 'price-low':
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price-high':
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;

                case 'title-az':
                case 'title_az':
                    $query->orderBy('title', 'asc');
                    break;

                case 'newest':
                default:
                    $query->latest();
                    break;
            }
        }

        $listings = $query->get();

        $userId = $this->getUserIdFromBearerToken($request);

        return response()->json(
            $this->addInteractionState($listings, $userId)
        );
    }

    public function random(Request $request)
    {
        $limit = (int) $request->get('limit', 3);

        if ($limit < 1) {
            $limit = 3;
        }

        if ($limit > 12) {
            $limit = 12;
        }

        $listings = Listing::with('images')
            ->where(function ($query) {
                $query->where('status', 'available')
                    ->orWhereNull('status');
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return response()->json($listings);
    }

    public function show(Request $request, Listing $listing)
    {
        $listing->load(['images', 'user']);

        $userId = $this->getUserIdFromBearerToken($request);

        $listing->liked_by_me = false;
        $listing->favorited_by_me = false;

        if ($userId) {
            $listing->liked_by_me = $listing->likes()
                ->where('user_id', $userId)
                ->exists();

            $listing->favorited_by_me = $listing->favorites()
                ->where('user_id', $userId)
                ->exists();
        }

        return response()->json($listing);
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
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'gender' => 'required|string|in:men,women',
            'brand' => 'required|string',
            'color' => 'required|string',
            'size' => 'required|string',
            'condition' => 'required|string',
            'images' => 'required|array|min:1|max:8',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $categoryExists = Category::where('name', $data['category'])->exists();

        if (!$categoryExists) {
            return response()->json([
                'message' => 'Selected category does not exist',
            ], 422);
        }

        unset($data['images']);

        $data['status'] = 'available';

        $listing = $user->listings()->create($data);

        foreach ($request->file('images') as $image) {
            $path = $image->store('listings', 'public');

            $listing->images()->create([
                'image_path' => $path,
            ]);
        }

        return response()->json($listing->load(['images', 'user']), 201);
    }

    public function update(Request $request, Listing $listing)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->role !== 'admin' && $listing->user_id !== $user->id) {
            return response()->json([
                'message' => 'No permission',
            ], 403);
        }

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $newPrice = (float) $data['price'];
        $currentPrice = (float) $listing->price;

        if ($newPrice < $currentPrice) {
            $listing->update([
                'original_price' => $listing->original_price ?: $currentPrice,
                'price' => $newPrice,
            ]);
        } else {
            $listing->update([
                'price' => $newPrice,
                'original_price' => null,
            ]);
        }

        return response()->json($listing->load(['images', 'user']));
    }

    public function destroy(Request $request, Listing $listing)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->role !== 'admin' && $listing->user_id !== $user->id) {
            return response()->json([
                'message' => 'No permission',
            ], 403);
        }

        $listing->delete();

        return response()->json([
            'message' => 'Listing deleted',
        ]);
    }

    public function userListings(\App\Models\User $user)
    {
        return response()->json(
            $user->listings()
                ->with('images')
                ->latest()
                ->get()
        );
    }

    public function purchase(Request $request, Listing $listing)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($listing->status === 'sold') {
            return response()->json([
                'message' => 'This listing is already sold.',
            ], 422);
        }

        if ($listing->user_id === $user->id) {
            return response()->json([
                'message' => 'You cannot buy your own listing.',
            ], 422);
        }

        $listing->update([
            'status' => 'sold',
        ]);

        Purchase::create([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
        ]);

        return response()->json([
            'message' => 'Listing purchased successfully.',
            'listing' => $listing->load(['images', 'user']),
        ]);
    }
}