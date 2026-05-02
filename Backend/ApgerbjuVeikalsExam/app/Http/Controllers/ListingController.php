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
        'user_id' => 'required|exists:users,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category' => 'required|string',
        'brand' => 'nullable|string',
        'color' => 'nullable|string',
        'size' => 'nullable|string',
        'condition' => 'required|string',
    ]);

    return Listing::create($data);
}


    public function show(Listing $listing)
    {
        return $listing;
    }



    public function update(Request $request, Listing $listing)
    {
    $data = $request->validate([
        'price' => 'required|numeric|min:0',
    ]);

    $listing->update($data);

    return response()->json($listing);
}



    public function destroy(Listing $listing)
    {
        $listing->delete();

        return response()->json(['message' => ' Listing deleted']);
    }
}