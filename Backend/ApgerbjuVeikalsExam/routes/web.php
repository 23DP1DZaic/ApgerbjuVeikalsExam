<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/products', function () {
    return [
        ['id' => 1, 'name' => 'Футболка', 'price' => 20],
        ['id' => 2, 'name' => 'Джинсы', 'price' => 50],
        ['id' => 3, 'name' => 'Куртка', 'price' => 120],
    ];
});