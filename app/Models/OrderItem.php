<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'product_id', 'product_name_snap', 'price_snap', 'qty', 'subtotal',
    ];

    protected $casts = [
        'price_snap' => 'float',
        'subtotal' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}