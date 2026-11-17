<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'status', 'avatar',
    ];

    protected $hidden = ['password'];

    public static function createGuest(string $name, ?string $phone): self
    {
        return static::create([
            'name' => $name,
            'email' => 'guest+'.Str::uuid().'@camrent.invalid',
            'password' => Hash::make(Str::random(64)),
            'phone' => $phone,
            'role' => 'user',
            'status' => 'Aktif',
        ]);
    }

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

    public function canRent(): bool
    {
        return in_array($this->role, ['user', 'mitra'], true);
    }
}