<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingLike;
use App\Models\ListingFavorite;
use Illuminate\Http\Request;

class ListingInteractionController extends Controller
{
    public function toggleLike(Request $request, Listing $listing)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $existingLike = ListingLike::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            return response()->json([
                'message' => 'Like removed',
                'liked' => false,
                'likes_count' => $listing->likes()->count(),
            ]);
        }

        ListingLike::create([
            'listing_id' => $listing->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Listing liked',
            'liked' => true,
            'likes_count' => $listing->likes()->count(),
        ]);
    }

    public function toggleFavorite(Request $request, Listing $listing)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $existingFavorite = ListingFavorite::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFavorite) {
            $existingFavorite->delete();

            return response()->json([
                'message' => 'Favorite removed',
                'favorited' => false,
                'favorites_count' => $listing->favorites()->count(),
            ]);
        }

        ListingFavorite::create([
            'listing_id' => $listing->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Listing added to favorites',
            'favorited' => true,
            'favorites_count' => $listing->favorites()->count(),
        ]);
    }

    public function myFavorites(Request $request)
    {
        $user = $request->user();

        return response()->json(
            $user->favoriteListings()
                ->with('images')
                ->latest()
                ->get()
        );
    }

    public function myLikes(Request $request)
    {
        $user = $request->user();

        return response()->json(
            $user->likedListings()
                ->with('images')
                ->latest()
                ->get()
        );
    }
}