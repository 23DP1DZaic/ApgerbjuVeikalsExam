<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function profile(User $user)
    {
        $user->load([
            'listings.images',
            'receivedReviews.listing.images',
            'receivedReviews.buyer',
        ]);

        if (!$user->hide_favorites) {
            $user->load('favoriteListings.images');
        }

        if (!$user->hide_likes) {
            $user->load('likedListings.images');
        }

        $favorites = $user->hide_favorites
            ? []
            : $user->favoriteListings;

        $liked = $user->hide_likes
            ? []
            : $user->likedListings;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'display_name' => $user->display_name,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_url,

            'hide_likes' => $user->hide_likes,
            'hide_favorites' => $user->hide_favorites,

            'listings' => $user->listings,
            'favorites' => $favorites,
            'liked' => $liked,

            'reviews' => $user->receivedReviews,

            'counts' => [
                'listings' => $user->listings->count(),
                'favorites' => $user->hide_favorites ? 0 : $favorites->count(),
                'liked' => $user->hide_likes ? 0 : $liked->count(),
                'purchases' => 0,
                'reviews' => $user->receivedReviews->count(),
            ],
        ]);
    }
}