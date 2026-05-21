<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $received = Offer::with(['listing.images', 'buyer'])
            ->where('seller_id', $user->id)
            ->latest()
            ->get();

        $sent = Offer::with(['listing.images', 'seller'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'received' => $received,
            'sent' => $sent,
        ]);
    }

    public function store(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($listing->status === 'sold') {
            return response()->json([
                'message' => 'This listing is already sold.',
            ], 422);
        }

        if ($listing->user_id === $user->id) {
            return response()->json([
                'message' => 'You cannot make an offer on your own listing.',
            ], 422);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $offer = Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
            'amount' => $data['amount'],
            'status' => 'pending',
            'seller_expires_at' => now()->addHours(24),
        ]);

        return response()->json(
            $offer->load(['listing.images', 'buyer', 'seller']),
            201
        );
    }

    public function accept(Request $request, Offer $offer)
    {
        $user = $request->user();

        if ($offer->seller_id !== $user->id) {
            return response()->json([
                'message' => 'No permission.',
            ], 403);
        }

        if ($offer->status !== 'pending') {
            return response()->json([
                'message' => 'This offer is not pending.',
            ], 422);
        }

        if ($offer->seller_expires_at && now()->greaterThan($offer->seller_expires_at)) {
            $offer->update([
                'status' => 'expired',
            ]);

            return response()->json([
                'message' => 'This offer has expired.',
            ], 422);
        }

        $listing = $offer->listing;

        if ($listing->status === 'sold') {
            return response()->json([
                'message' => 'This listing is already sold.',
            ], 422);
        }

        $listing->update([
            'original_price' => $listing->original_price ?: $listing->price,
            'price' => $offer->amount,
        ]);

        $offer->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'buyer_expires_at' => now()->addHours(24),
        ]);

        Offer::where('listing_id', $listing->id)
            ->where('id', '!=', $offer->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'declined',
                'declined_at' => now(),
            ]);

        return response()->json(
            $offer->fresh()->load(['listing.images', 'buyer', 'seller'])
        );
    }

    public function decline(Request $request, Offer $offer)
    {
        $user = $request->user();

        if ($offer->seller_id !== $user->id) {
            return response()->json([
                'message' => 'No permission.',
            ], 403);
        }

        if ($offer->status !== 'pending') {
            return response()->json([
                'message' => 'This offer is not pending.',
            ], 422);
        }

        $offer->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);

        return response()->json(
            $offer->fresh()->load(['listing.images', 'buyer', 'seller'])
        );
    }

    public function pay(Request $request, Offer $offer)
    {
        $user = $request->user();

        if ($offer->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'No permission.',
            ], 403);
        }

        if ($offer->status !== 'accepted') {
            return response()->json([
                'message' => 'This offer is not accepted.',
            ], 422);
        }

        if ($offer->buyer_expires_at && now()->greaterThan($offer->buyer_expires_at)) {
            $offer->update([
                'status' => 'expired',
            ]);

            return response()->json([
                'message' => 'Payment time expired.',
            ], 422);
        }

        $listing = $offer->listing;

        if ($listing->status === 'sold') {
            return response()->json([
                'message' => 'This listing is already sold.',
            ], 422);
        }

        $listing->update([
            'status' => 'sold',
            'price' => $offer->amount,
        ]);

        $offer->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'message' => 'Offer paid successfully.',
            'listing' => $listing->load(['images', 'user']),
            'offer' => $offer->fresh(),
        ]);
    }

    public function show(Request $request, Offer $offer)
{
    $user = $request->user();

    if ($offer->buyer_id !== $user->id && $offer->seller_id !== $user->id) {
        return response()->json([
            'message' => 'No permission.',
        ], 403);
    }

    return response()->json(
        $offer->load(['listing.images', 'buyer', 'seller'])
    );
}
}