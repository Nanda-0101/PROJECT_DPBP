<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Foto tambahan (sudut lain) sebuah produk. Foto utama tetap di products.image.
 */
class ProductImage extends Model
{
    public $timestamps = false;

    protected $fillable = ['product_id', 'path', 'label', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
