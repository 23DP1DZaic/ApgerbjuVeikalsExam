<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
