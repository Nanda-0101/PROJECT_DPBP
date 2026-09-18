<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $user1  = \App\Models\User::where('email', 'user@camrent.id')->value('id');
        $user3  = \App\Models\User::where('email', 'dinda@camrent.id')->value('id');
        $prodId = fn(string $name) => \App\Models\Product::where('name', $name)->value('id');

        $rows = [
            ['BKG-1001', $user1, 'Sewa', '2026-09-05', '2026-09-06', '1 Hari', 'DP',          'QRIS',     'KTP', 480000, 144000, 'Selesai',             'Sony A7III',      480000, 1, 480000],
            ['BKG-1002', $user1, 'Sewa', '2026-09-14', '2026-09-14', '12 Jam', 'Bayar Penuh', 'Transfer', 'SIM', 190000, 190000, 'Dikonfirmasi',        'Aputure 120D II', 190000, 1, 190000],
            ['BKG-1003', $user3, 'Sewa', '2026-09-20', '2026-09-22', '2 Hari', 'DP',          'Cash',     'KTP', 500000, 150000, 'Menunggu Konfirmasi', 'DJI RS3 Gimbal',  250000, 2, 500000],
        ];

        foreach ($rows as $r) {
            [$code,$uid,$type,$start,$end,$dur,$ptype,$pmethod,$guar,$total,$paid,$status,$prodName,$price,$qty,$subtotal] = $r;

            $booking = Booking::create([
                'code' => $code, 'user_id' => $uid, 'type' => $type,
                'start_date' => $start, 'end_date' => $end, 'duration_label' => $dur,
                'payment_type' => $ptype, 'payment_method' => $pmethod, 'guarantee' => $guar,
                'total_amount' => $total, 'paid_amount' => $paid, 'status' => $status,
            ]);

            OrderItem::create([
                'booking_id'        => $booking->id,
                'product_id'        => $prodId($prodName),
                'product_name_snap' => $prodName,
                'price_snap'        => $price,
                'qty'               => $qty,
                'subtotal'          => $subtotal,
            ]);
        }
    }
}