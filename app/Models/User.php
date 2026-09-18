<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'status', 'avatar',
    ];

    protected $hidden = ['password'];

    // ==== Relations ====
    public function mitraProfile()
    {
        return $this->hasOne(MitraProfile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // ==== Helpers ====
    public function isMitra(): bool
    {
        return $this->role === 'mitra';
    }
}