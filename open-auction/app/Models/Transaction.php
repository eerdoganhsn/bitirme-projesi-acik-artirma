<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}
