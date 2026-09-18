<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'code', 'user_id', 'type', 'start_date', 'end_date', 'duration_label',
        'payment_type', 'payment_method', 'guarantee', 'total_amount', 'paid_amount',
        'status', 'note', 'address',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'float',
        'paid_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // ==== Helpers ====
    public static function generateCode(string $prefix = 'BKG'): string
    {
        return $prefix . '-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }
}