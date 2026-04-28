<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'shop_id', 
        'category_id', 
        'title', 
        'description', 
        'listing_type', 
        'buy_now_price', 
        'status'
    ];
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function auction()
    {
        return $this->hasOne(Auction::class);
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'watchlists')
                    ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function coverImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_cover', true);
    }
    public function comments() { return $this->hasMany(Comment::class)->latest(); }
}
