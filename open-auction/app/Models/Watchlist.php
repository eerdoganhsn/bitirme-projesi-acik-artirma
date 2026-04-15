<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    public function watchlist()
    {
        return $this->belongsToMany(Product::class, 'watchlists')
                    ->withTimestamps();
    }
}
