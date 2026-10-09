<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';
    
    protected $fillable = [
        'vendor_id',
        'name',
        'category',
        'price',
        'quantity',
        'expiry_date',
        'status',
        'image'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
        return $this->belongsTo(User::class, 'vendor_id');
    }
    
}
