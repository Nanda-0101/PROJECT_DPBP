<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraProfile extends Model
{
    protected $table = 'mitra_profiles';

    protected $fillable = [
        'user_id', 'owner_name', 'nik', 'category', 'city', 'bank_account', 'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(MitraDocument::class, 'mitra_id');
    }
}