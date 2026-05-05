<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // İzleme listesindeki ürünün detaylarını çekmek için ilişki
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // İzleyen kullanıcı bilgisi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}