<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{   
    protected $fillable = [
        'product_id', 
        'starting_price', 
        'current_price', 
        'start_time',     
        'end_time',       
        'extension_count',
        'status'
    ];
    
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
