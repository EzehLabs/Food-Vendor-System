<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'reference',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
