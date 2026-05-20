<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function profile(User $user)
    {
        $user->load([
            'listings.images',
            'favoriteListings.images',
            'likedListings.images',
            'receivedReviews.listing.images',
            'receivedReviews.buyer',
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'display_name' => $user->display_name,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_url,

            'listings' => $user->listings,
            'favorites' => $user->favoriteListings,
            'liked' => $user->likedListings,

            'reviews' => $user->receivedReviews,

            'counts' => [
                'listings' => $user->listings->count(),
                'favorites' => $user->favoriteListings->count(),
                'liked' => $user->likedListings->count(),
                'purchases' => 0,
                'reviews' => $user->receivedReviews->count(),
            ],
        ]);
    }
}