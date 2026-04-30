<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index()
    {
        return Listing::latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            'condition' => 'required|string',
        ]);

        $data['user_id'] = 1;

        return Listing::create($data);
    }

    public function show(Listing $listing)
    {
        return $listing;
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();

        return response()->json(['message' => 'Deleted']);
    }
}