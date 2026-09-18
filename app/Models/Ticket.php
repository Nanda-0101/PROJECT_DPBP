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
        return 'TCK-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}