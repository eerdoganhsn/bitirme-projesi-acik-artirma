<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class)->orderBy('amount', 'desc');
    }

    public function proxyBids()
    {
        return $this->hasMany(ProxyBid::class);
    }
}
