<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'code', 'user_id', 'booking_id', 'category', 'name', 'contact',
        'description', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TCK-' . random_int(10000000, 99999999);
        } while (static::where('code', $code)->exists());

        return $code;
    }
}