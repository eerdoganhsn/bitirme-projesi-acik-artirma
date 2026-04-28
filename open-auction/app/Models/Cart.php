<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // Bu satır eksik olduğu için hata alıyorsun:
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity'
    ];

    // İlişkileri de buraya ekleyelim ki sepet sayfasında hata almayasın:
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}