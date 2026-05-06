<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ListingImage;

class Listing extends Model
{
        protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'category',
        'brand',
        'color',
        'size',
        'condition',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }
}
