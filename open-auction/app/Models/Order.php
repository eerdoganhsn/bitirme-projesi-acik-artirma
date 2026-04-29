<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Dışarıdan doldurulabilir alanları buraya ekliyoruz
    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'full_name',
        'address',
        'city',
        'phone',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
