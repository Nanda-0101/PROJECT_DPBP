<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'seller_id', 'category_id', 'name', 'brand', 'icon', 'image',
        'description', 'price_per_half_day', 'price_per_day', 'price_sale',
        'stock', 'rating', 'reviews_count', 'is_trending', 'is_verified', 'status',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_verified' => 'boolean',
        'rating' => 'float',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function specs()
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==== Scopes ====
    public function scopeRentable($q)
    {
        return $q->where('price_per_day', '>', 0);
    }

    public function scopeSellable($q)
    {
        return $q->where('price_sale', '>', 0);
    }

    public function scopeTrending($q)
    {
        return $q->where('is_trending', true);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'Aktif')->where('stock', '>', 0);
    }
}