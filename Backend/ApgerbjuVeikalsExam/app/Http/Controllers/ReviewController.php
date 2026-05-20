<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function myPurchases(Request $request)
    {
        $user = $request->user();

        $purchases = Purchase::with(['listing.images', 'seller'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($purchase) use ($user) {
                $review = Review::with(['listing.images', 'buyer', 'seller'])
                    ->where('listing_id', $purchase->listing_id)
                    ->where('buyer_id', $user->id)
                    ->first();

                return [
                    'id' => $purchase->id,
                    'listing' => $purchase->listing,
                    'seller' => $purchase->seller,
                    'already_reviewed' => $review !== null,
                    'review' => $review,
                ];
            });

        return response()->json($purchases);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|min:5|max:1000',
        ]);

        $purchase = Purchase::where('listing_id', $data['listing_id'])
            ->where('buyer_id', $user->id)
            ->first();

        if (!$purchase) {
            return response()->json([
                'message' => 'You can review only purchased listings.',
            ], 403);
        }

        $existingReview = Review::where('listing_id', $data['listing_id'])
            ->where('buyer_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You already reviewed this listing.',
            ], 422);
        }

        $review = Review::create([
            'listing_id' => $data['listing_id'],
            'buyer_id' => $user->id,
            'seller_id' => $purchase->seller_id,
            'rating' => $data['rating'],
            'text' => $data['text'],
        ]);

        return response()->json(
            $review->load(['listing.images', 'buyer', 'seller']),
            201
        );
    }
}