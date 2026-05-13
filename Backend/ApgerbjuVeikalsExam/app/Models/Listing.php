<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ListingImage;
use App\Models\ListingLike;
use App\Models\ListingFavorite;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'category',
        'gender',
        'brand',
        'color',
        'size',
        'condition',
    ];

    protected $appends = [
        'likes_count',
        'favorites_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function likes()
    {
        return $this->hasMany(ListingLike::class);
    }

    public function favorites()
    {
        return $this->hasMany(ListingFavorite::class);
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }
}