<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/api/products', function (Request $request) {
    $products = [
        ['id' => 1, 'name' => '1', 'price' => 29, 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab', 'category' => 'Tshirt'],
        ['id' => 2, 'name' => '2', 'price' => 89, 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d', 'category' => 'Jeans'],
        ['id' => 3, 'name' => '3', 'price' => 65, 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7', 'category' => 'Hoodie'],
        ['id' => 4, 'name' => '4', 'price' => 120, 'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5', 'category' => 'Jacket'],
        ['id' => 5, 'name' => '5', 'price' => 55, 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c', 'category' => 'Shirt'],
        ['id' => 6, 'name' => '6', 'price' => 25, 'image' => 'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9', 'category' => 'Tshirt'],
    ];

    return response()->json($products);
});
