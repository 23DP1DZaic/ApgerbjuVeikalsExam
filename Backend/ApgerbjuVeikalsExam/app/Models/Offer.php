<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'amount',
        'status',
        'seller_expires_at',
        'buyer_expires_at',
        'accepted_at',
        'declined_at',
        'paid_at',
    ];

    protected $casts = [
        'seller_expires_at' => 'datetime',
        'buyer_expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}