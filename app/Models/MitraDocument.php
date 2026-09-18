<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraDocument extends Model
{
    protected $table = 'mitra_documents';
    public $timestamps = false;

    protected $fillable = ['mitra_id', 'photo_url', 'sort_order'];

    public function mitra()
    {
        return $this->belongsTo(MitraProfile::class, 'mitra_id');
    }
}